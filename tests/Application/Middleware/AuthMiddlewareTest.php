<?php

declare(strict_types=1);

namespace Tests\Application\Middleware;

use App\Application\Auth\AuthSession;
use App\Application\Middleware\AuthMiddleware;
use App\Domain\User\Role;
use App\Domain\User\StatusAkun;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response as SlimResponse;
use Tests\Support\FakeUserRepository;
use Tests\TestCase;

class AuthMiddlewareTest extends TestCase
{
    private FakeUserRepository $userRepo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = new FakeUserRepository();
    }

    private function createHandler(?ServerRequestInterface &$capturedRequest = null): RequestHandlerInterface
    {
        return new class ($capturedRequest) implements RequestHandlerInterface {
            private ?ServerRequestInterface $captured;

            public function __construct(?ServerRequestInterface &$captured)
            {
                $this->captured = &$captured;
            }

            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $this->captured = $request;
                return new SlimResponse(200);
            }
        };
    }

    private function createSession(array &$data): AuthSession
    {
        return new AuthSession($data, fn () => null, fn () => null);
    }

    public function testWithoutSessionReturns401(): void
    {
        $middleware = new AuthMiddleware($this->userRepo);
        $request = $this->createRequest('GET', '/test');

        $response = $middleware->process($request, $this->createHandler());
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testSessionWithoutUserIdReturns401(): void
    {
        $middleware = new AuthMiddleware($this->userRepo);
        $data = [];
        $request = $this->createRequest('GET', '/test')
            ->withAttribute('session', $this->createSession($data));

        $response = $middleware->process($request, $this->createHandler());
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testUserIdNotFoundInRepoLogsOutAndReturns401(): void
    {
        $middleware = new AuthMiddleware($this->userRepo);
        $data = ['user_id' => 999];
        $session = $this->createSession($data);
        $request = $this->createRequest('GET', '/test')
            ->withAttribute('session', $session);

        $response = $middleware->process($request, $this->createHandler());
        $this->assertSame(401, $response->getStatusCode());
        $this->assertNull($session->userId());
    }

    public function testInactiveUserLogsOutAndReturns401WithStatusChangedMessage(): void
    {
        $user = new User(
            1,
            'Nonaktif',
            null,
            'nonaktif@example.com',
            'hash',
            Role::MASYARAKAT,
            5,
            StatusAkun::NONAKTIF,
            'enc',
            'enc',
            '2026-11-09 10:00:00'
        );
        $this->userRepo->addUserDirectly($user, 'hash-nip');

        $middleware = new AuthMiddleware($this->userRepo);
        $data = ['user_id' => 1];
        $session = $this->createSession($data);
        $request = $this->createRequest('GET', '/test')
            ->withAttribute('session', $session);

        $response = $middleware->process($request, $this->createHandler());
        $this->assertSame(401, $response->getStatusCode());
        $this->assertNull($session->userId());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Sesi berakhir karena status akun berubah.', $body);
    }

    public function testActiveUserAttachesUserAttributeAndPasses(): void
    {
        $user = new User(
            1,
            'Aktif',
            null,
            'aktif@example.com',
            'hash',
            Role::MASYARAKAT,
            5,
            StatusAkun::AKTIF,
            'enc',
            'enc',
            '2026-11-09 10:00:00'
        );
        $this->userRepo->addUserDirectly($user, 'hash-nip');

        $middleware = new AuthMiddleware($this->userRepo);
        $data = ['user_id' => 1];
        $session = $this->createSession($data);
        $request = $this->createRequest('GET', '/test')
            ->withAttribute('session', $session);

        $captured = null;
        $response = $middleware->process($request, $this->createHandler($captured));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertNotNull($captured);
        $this->assertSame(1, $captured->getAttribute('user')->getId());
    }
}
