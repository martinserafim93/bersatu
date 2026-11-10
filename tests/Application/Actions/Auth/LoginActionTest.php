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

class LoginActionTest extends TestCase
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

    public function testLoginSuccessSetsSessionUserIdAndReturnsUser(): void
    {
        $user = new User(
            1,
            'User Aktif',
            null,
            'aktif@example.com',
            password_hash('sandi123', PASSWORD_DEFAULT),
            Role::MASYARAKAT,
            5,
            StatusAkun::AKTIF,
            $this->cipher->encrypt('6501051234560001'),
            $this->cipher->encrypt('6281234567890'),
            '2026-11-09 10:00:00'
        );
        $this->userRepo->addUserDirectly($user, $this->cipher->blindIndex('6501051234560001'));

        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $sessionData = [];
        $session = new AuthSession($sessionData, fn () => null, fn () => null);

        $request = $this->createRequest('POST', '/api/auth/login', [
            'HTTP_ACCEPT' => 'application/json',
            'Content-Type' => 'application/json',
        ])
            ->withAttribute('session', $session)
            ->withParsedBody(['email' => 'aktif@example.com', 'password' => 'sandi123']);

        $response = $app->handle($request);
        $this->assertSame(200, $response->getStatusCode());

        $this->assertSame(1, $session->userId());

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertSame(1, $payload['data']['user']['id']);
        $this->assertSame('aktif@example.com', $payload['data']['user']['email']);
    }

    public function testLoginWrongPasswordReturns401(): void
    {
        $user = new User(
            1,
            'User Aktif',
            null,
            'aktif@example.com',
            password_hash('sandi123', PASSWORD_DEFAULT),
            Role::MASYARAKAT,
            5,
            StatusAkun::AKTIF,
            $this->cipher->encrypt('6501051234560001'),
            $this->cipher->encrypt('6281234567890'),
            '2026-11-09 10:00:00'
        );
        $this->userRepo->addUserDirectly($user, $this->cipher->blindIndex('6501051234560001'));

        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $sessionData = [];
        $session = new AuthSession($sessionData, fn () => null, fn () => null);

        $request = $this->createRequest('POST', '/api/auth/login', [
            'HTTP_ACCEPT' => 'application/json',
            'Content-Type' => 'application/json',
        ])
            ->withAttribute('session', $session)
            ->withParsedBody(['email' => 'aktif@example.com', 'password' => 'sandi_salah']);

        $response = $app->handle($request);
        $this->assertSame(401, $response->getStatusCode());
        $this->assertNull($session->userId());
    }

    public function testLoginInactiveAccountReturns403WithVerificationError(): void
    {
        $user = new User(
            1,
            'User Pending',
            null,
            'pending@example.com',
            password_hash('sandi123', PASSWORD_DEFAULT),
            Role::MASYARAKAT,
            5,
            StatusAkun::MENUNGGU_VERIFIKASI,
            $this->cipher->encrypt('6501051234560001'),
            $this->cipher->encrypt('6281234567890'),
            '2026-11-09 10:00:00'
        );
        $this->userRepo->addUserDirectly($user, $this->cipher->blindIndex('6501051234560001'));

        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $sessionData = [];
        $session = new AuthSession($sessionData, fn () => null, fn () => null);

        $request = $this->createRequest('POST', '/api/auth/login', [
            'HTTP_ACCEPT' => 'application/json',
            'Content-Type' => 'application/json',
        ])
            ->withAttribute('session', $session)
            ->withParsedBody(['email' => 'pending@example.com', 'password' => 'sandi123']);

        $response = $app->handle($request);
        $this->assertSame(403, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertSame('VERIFICATION_ERROR', $payload['error']['type']);
        $this->assertNull($session->userId());
    }

    public function testLoginNonObjectBodyReturns400(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $request = $this->createRequest('POST', '/api/auth/login', [
            'HTTP_ACCEPT' => 'application/json',
            'Content-Type' => 'application/json',
        ])->withParsedBody(null);

        $response = $app->handle($request);
        $this->assertSame(400, $response->getStatusCode());
    }
}
