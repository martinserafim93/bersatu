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

class MeActionTest extends TestCase
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

    public function testMeWithoutSessionReturns401(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $request = $this->createRequest('GET', '/api/auth/me');
        $response = $app->handle($request);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testMeWithSessionReturnsSerializedUserWithoutSensitiveFields(): void
    {
        $user = new User(
            1,
            'Ahmad Fauzi',
            'KUA',
            'ahmad@example.com',
            '$2y$10$hashedpassword',
            Role::KUA,
            5,
            StatusAkun::AKTIF,
            'enc-nip-nik',
            'enc-no-wa',
            '2026-11-09 10:00:00'
        );
        $this->userRepo->addUserDirectly($user, 'hash-nip');

        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $sessionData = ['user_id' => 1];
        $session = new AuthSession($sessionData, fn () => null, fn () => null);

        $request = $this->createRequest('GET', '/api/auth/me')
            ->withAttribute('session', $session);

        $response = $app->handle($request);
        $this->assertSame(200, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);
        $userData = $payload['data']['user'];

        $this->assertSame(1, $userData['id']);
        $this->assertSame('Ahmad Fauzi', $userData['nama_lengkap']);
        $this->assertSame('ahmad@example.com', $userData['email']);

        $jsonRaw = (string) $response->getBody();
        $this->assertStringNotContainsString('password', strtolower($jsonRaw));
        $this->assertStringNotContainsString('enc-nip-nik', $jsonRaw);
        $this->assertStringNotContainsString('enc-no-wa', $jsonRaw);
        $this->assertArrayNotHasKey('password_hash', $userData);
        $this->assertArrayNotHasKey('nip_nik_encrypted', $userData);
        $this->assertArrayNotHasKey('no_wa_encrypted', $userData);
    }
}
