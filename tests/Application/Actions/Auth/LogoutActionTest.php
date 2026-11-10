<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Auth;

use App\Application\Auth\AuthSession;
use App\Domain\User\Role;
use App\Domain\User\StatusAkun;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Domain\Wilayah\WilayahRepository;
use App\Infrastructure\Security\IdentityCipher;
use DI\Container;
use Tests\Support\FakeUserRepository;
use Tests\TestCase;

class LogoutActionTest extends TestCase
{
    private FakeUserRepository $userRepo;
    private IdentityCipher $cipher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = new FakeUserRepository();
        $this->cipher = new IdentityCipher(str_repeat('ab', 32));
    }

    private function configureContainer(Container $container): void
    {
        $wilayahRepo = $this->prophesize(WilayahRepository::class)->reveal();
        /** @var WilayahRepository $wilayahRepo */
        $container->set(UserRepository::class, $this->userRepo);
        $container->set(WilayahRepository::class, $wilayahRepo);
        $container->set(IdentityCipher::class, $this->cipher);
    }

    public function testLogoutWithoutSessionReturns401(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $request = $this->createRequest('POST', '/api/auth/logout', [
            'HTTP_ACCEPT' => 'application/json',
            'Content-Type' => 'application/json',
        ])->withParsedBody([]);

        $response = $app->handle($request);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testLogoutWithActiveSessionClearsDataAndReturns200(): void
    {
        $user = new User(
            1,
            'User',
            null,
            'user@example.com',
            'hash',
            Role::MASYARAKAT,
            5,
            StatusAkun::AKTIF,
            'enc',
            'enc',
            '2026-11-09 10:00:00'
        );
        $this->userRepo->addUserDirectly($user, 'hash');

        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $sessionData = ['user_id' => 1];
        $session = new AuthSession($sessionData, fn () => null, fn () => null);

        $request = $this->createRequest('POST', '/api/auth/logout', [
            'HTTP_ACCEPT' => 'application/json',
            'Content-Type' => 'application/json',
        ])
            ->withAttribute('session', $session)
            ->withParsedBody([]);

        $response = $app->handle($request);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertNull($session->userId());
        $this->assertEmpty($sessionData);
    }
}
