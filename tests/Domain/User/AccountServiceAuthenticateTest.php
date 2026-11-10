<?php

declare(strict_types=1);

namespace Tests\Domain\User;

use App\Domain\User\AccountNotActiveException;
use App\Domain\User\AccountService;
use App\Domain\User\AuthenticationFailedException;
use App\Domain\User\Role;
use App\Domain\User\StatusAkun;
use App\Domain\User\User;
use App\Domain\Wilayah\WilayahRepository;
use App\Infrastructure\Security\IdentityCipher;
use Tests\Support\FakeUserRepository;
use Tests\TestCase;

class AccountServiceAuthenticateTest extends TestCase
{
    private FakeUserRepository $userRepo;
    private IdentityCipher $cipher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = new FakeUserRepository();
        $this->cipher = new IdentityCipher(str_repeat('ab', 32));
    }

    private function createService(): AccountService
    {
        $wilayahRepo = $this->prophesize(WilayahRepository::class)->reveal();
        /** @var WilayahRepository $wilayahRepo */
        return new AccountService($this->userRepo, $wilayahRepo, $this->cipher);
    }

    private function seedUser(int $id, string $email, string $status, string $plainPassword = 'password123'): User
    {
        $user = new User(
            $id,
            'Pengguna ' . $id,
            null,
            $email,
            password_hash($plainPassword, PASSWORD_DEFAULT),
            Role::MASYARAKAT,
            5,
            $status,
            $this->cipher->encrypt('650105123456000' . $id),
            $this->cipher->encrypt('628123456789' . $id),
            '2026-11-09 10:00:00'
        );
        $this->userRepo->addUserDirectly($user, $this->cipher->blindIndex('650105123456000' . $id));
        return $user;
    }

    public function testAuthenticateSuccessWithActiveUser(): void
    {
        $this->seedUser(1, 'aktif@example.com', StatusAkun::AKTIF, 'rahasia123');
        $service = $this->createService();

        $user = $service->authenticate('  AKTIF@example.com  ', 'rahasia123');
        $this->assertSame(1, $user->getId());
        $this->assertSame('aktif@example.com', $user->getEmail());
    }

    public function testAuthenticateFailsOnWrongPassword(): void
    {
        $this->seedUser(1, 'aktif@example.com', StatusAkun::AKTIF, 'rahasia123');
        $service = $this->createService();

        $this->expectException(AuthenticationFailedException::class);
        $service->authenticate('aktif@example.com', 'salah_password');
    }

    public function testAuthenticateFailsOnMissingEmail(): void
    {
        $service = $this->createService();

        $this->expectException(AuthenticationFailedException::class);
        $service->authenticate('tidakada@example.com', 'rahasia123');
    }

    public function testAuthenticateThrowsAccountNotActiveWhenPasswordCorrect(): void
    {
        $statuses = [
            StatusAkun::MENUNGGU_VERIFIKASI => 'Akun Anda masih menunggu verifikasi superadmin.',
            StatusAkun::DITOLAK => 'Pendaftaran akun Anda ditolak. Hubungi pengelola portal.',
            StatusAkun::NONAKTIF => 'Akun Anda dinonaktifkan. Hubungi pengelola portal.',
        ];

        $id = 10;
        foreach ($statuses as $status => $expectedMessage) {
            $id++;
            $email = "user_{$status}@example.com";
            $this->seedUser($id, $email, $status, 'passwordBenar');
            $service = $this->createService();

            try {
                $service->authenticate($email, 'passwordBenar');
                $this->fail("Status {$status} harus melempar AccountNotActiveException");
            } catch (AccountNotActiveException $e) {
                $this->assertSame($status, $e->getStatusAkun());
                $this->assertSame($expectedMessage, $e->getMessage());
            }
        }
    }

    public function testAuthenticateThrowsAuthFailedWhenInactiveAccountHasWrongPassword(): void
    {
        $this->seedUser(1, 'pending@example.com', StatusAkun::MENUNGGU_VERIFIKASI, 'passwordBenar');
        $service = $this->createService();

        $this->expectException(AuthenticationFailedException::class);
        $service->authenticate('pending@example.com', 'passwordSalah');
    }

    /**
     * @dataProvider invalidInputProvider
     */
    public function testAuthenticateFailsOnInvalidInputTypes(mixed $email, mixed $password): void
    {
        $service = $this->createService();

        $this->expectException(AuthenticationFailedException::class);
        $service->authenticate($email, $password);
    }

    /**
     * @return array<string, array{0: mixed, 1: mixed}>
     */
    public function invalidInputProvider(): array
    {
        return [
            'empty_email' => ['', 'password123'],
            'whitespace_email' => ['   ', 'password123'],
            'empty_password' => ['user@example.com', ''],
            'non_string_email' => [123, 'password123'],
            'non_string_password' => ['user@example.com', ['array']],
            'null_email' => [null, 'password123'],
        ];
    }
}
