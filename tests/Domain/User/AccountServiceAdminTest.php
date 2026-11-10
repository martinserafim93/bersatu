<?php

declare(strict_types=1);

namespace Tests\Domain\User;

use App\Domain\User\AccountConflictException;
use App\Domain\User\AccountService;
use App\Domain\User\Role;
use App\Domain\User\StatusAkun;
use App\Domain\User\User;
use App\Domain\User\UserNotFoundException;
use App\Domain\User\ValidationException;
use App\Domain\Wilayah\Wilayah;
use App\Domain\Wilayah\WilayahRepository;
use App\Infrastructure\Security\IdentityCipher;
use Tests\Support\FakeUserRepository;
use Tests\TestCase;

class AccountServiceAdminTest extends TestCase
{
    private FakeUserRepository $userRepo;
    private IdentityCipher $cipher;
    private Wilayah $mockWilayah;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = new FakeUserRepository();
        $this->cipher = new IdentityCipher(str_repeat('ab', 32));
        $this->mockWilayah = new Wilayah(
            5,
            '65.01.05',
            'Kabupaten Bulungan',
            'Tanjung Selor',
            2.8375,
            117.365278,
            null
        );
    }

    private function createService(): AccountService
    {
        $wilayahProphecy = $this->prophesize(WilayahRepository::class);
        $wilayahProphecy->findById(5)->willReturn($this->mockWilayah);
        /** @var WilayahRepository $wilayahRepo */
        $wilayahRepo = $wilayahProphecy->reveal();
        return new AccountService($this->userRepo, $wilayahRepo, $this->cipher);
    }

    private function seedUser(
        int $id,
        string $role,
        string $status,
        ?string $instansi = null,
        string $email = ''
    ): User {
        if ($email === '') {
            $email = "user{$id}@example.com";
        }
        $user = new User(
            $id,
            'User ' . $id,
            $instansi,
            $email,
            password_hash('password123', PASSWORD_DEFAULT),
            $role,
            5,
            $status,
            $this->cipher->encrypt('650105123456000' . $id),
            $this->cipher->encrypt('62812345678' . $id),
            '2026-11-09 10:00:00'
        );
        $this->userRepo->addUserDirectly($user, $this->cipher->blindIndex('650105123456000' . $id));
        return $user;
    }

    public function testApproveFromPendingAndRejected(): void
    {
        $this->seedUser(1, Role::MASYARAKAT, StatusAkun::MENUNGGU_VERIFIKASI);
        $this->seedUser(2, Role::MASYARAKAT, StatusAkun::DITOLAK);
        $service = $this->createService();

        $service->approve(1);
        $this->assertSame(StatusAkun::AKTIF, $this->userRepo->findById(1)->getStatusAkun());

        $service->approve(2);
        $this->assertSame(StatusAkun::AKTIF, $this->userRepo->findById(2)->getStatusAkun());
    }

    public function testApproveWithRoleCorrectionSuccess(): void
    {
        $this->seedUser(1, Role::MASYARAKAT, StatusAkun::MENUNGGU_VERIFIKASI, 'KUA Tanjung Palas');
        $service = $this->createService();

        $service->approve(1, Role::KUA);
        $updated = $this->userRepo->findById(1);
        $this->assertSame(StatusAkun::AKTIF, $updated->getStatusAkun());
        $this->assertSame(Role::KUA, $updated->getRole());
    }

    public function testApproveWithCorrectionToInternalRoleThrowsValidationException(): void
    {
        $this->seedUser(1, Role::MASYARAKAT, StatusAkun::MENUNGGU_VERIFIKASI, 'Instansi');
        $service = $this->createService();

        $this->expectException(ValidationException::class);
        $service->approve(1, Role::VERIFIKATOR);
    }

    public function testApproveWithCorrectionToFieldReporterWithoutInstansiThrowsValidationException(): void
    {
        $this->seedUser(1, Role::MASYARAKAT, StatusAkun::MENUNGGU_VERIFIKASI, null);
        $service = $this->createService();

        $this->expectException(ValidationException::class);
        $service->approve(1, Role::KUA);
    }

    public function testApproveWithCorrectionOnInternalUserThrowsAccountConflictException(): void
    {
        $this->seedUser(1, Role::VERIFIKATOR, StatusAkun::MENUNGGU_VERIFIKASI, 'Kanwil');
        $service = $this->createService();

        $this->expectException(AccountConflictException::class);
        $this->expectExceptionMessage('Peran akun internal tidak dapat dikoreksi.');
        $service->approve(1, Role::KUA);
    }

    public function testRejectOnlyFromPending(): void
    {
        $this->seedUser(1, Role::MASYARAKAT, StatusAkun::MENUNGGU_VERIFIKASI);
        $this->seedUser(2, Role::MASYARAKAT, StatusAkun::AKTIF);
        $service = $this->createService();

        $service->reject(1);
        $this->assertSame(StatusAkun::DITOLAK, $this->userRepo->findById(1)->getStatusAkun());

        $this->expectException(AccountConflictException::class);
        $service->reject(2);
    }

    public function testDeactivateAndReactivateTransitions(): void
    {
        $this->seedUser(1, Role::MASYARAKAT, StatusAkun::AKTIF);
        $this->seedUser(2, Role::MASYARAKAT, StatusAkun::NONAKTIF);
        $service = $this->createService();

        $service->deactivate(1);
        $this->assertSame(StatusAkun::NONAKTIF, $this->userRepo->findById(1)->getStatusAkun());

        try {
            $service->deactivate(1);
            $this->fail('Deactivate pada akun nonaktif harus melempar AccountConflictException');
        } catch (AccountConflictException $e) {
            $this->assertSame('Status akun saat ini tidak mengizinkan tindakan ini.', $e->getMessage());
        }

        $service->reactivate(2);
        $this->assertSame(StatusAkun::AKTIF, $this->userRepo->findById(2)->getStatusAkun());

        $this->expectException(AccountConflictException::class);
        $service->reactivate(2);
    }

    public function testAllActionsOnSuperadminThrowConflictException(): void
    {
        $this->seedUser(1, Role::SUPERADMIN, StatusAkun::AKTIF);
        $service = $this->createService();

        $actions = [
            fn () => $service->approve(1),
            fn () => $service->reject(1),
            fn () => $service->deactivate(1),
            fn () => $service->reactivate(1),
        ];

        foreach ($actions as $action) {
            try {
                $action();
                $this->fail('Aksi pada superadmin harus melempar AccountConflictException');
            } catch (AccountConflictException $e) {
                $this->assertSame('Akun superadmin tidak dapat diubah melalui antarmuka.', $e->getMessage());
            }
        }
    }

    public function testActionOnNonExistentUserThrowsNotFound(): void
    {
        $service = $this->createService();
        $this->expectException(UserNotFoundException::class);
        $service->approve(999);
    }

    public function testListAccountsWithAndWithoutFilter(): void
    {
        $this->seedUser(1, Role::MASYARAKAT, StatusAkun::MENUNGGU_VERIFIKASI);
        $this->seedUser(2, Role::KUA, StatusAkun::AKTIF, 'KUA Tanjung Palas');
        $service = $this->createService();

        $all = $service->listAccounts(null);
        $this->assertCount(2, $all);
        $this->assertSame('6501051234560001', $all[0]['nip_nik']);
        $this->assertSame('628123456781', $all[0]['no_wa']);
        $this->assertArrayNotHasKey('password_hash', $all[0]);

        $pending = $service->listAccounts(StatusAkun::MENUNGGU_VERIFIKASI);
        $this->assertCount(1, $pending);
        $this->assertSame(1, $pending[0]['id']);

        $this->expectException(ValidationException::class);
        $service->listAccounts('invalid_status');
    }

    public function testCreateInternalCreatesActiveUser(): void
    {
        $service = $this->createService();

        $id = $service->createInternal([
            'role' => Role::VERIFIKATOR,
            'nama_lengkap' => 'Verifikator Kanwil',
            'instansi' => 'Bidang Urusan Agama',
            'nip_nik' => '198001012005011001',
            'no_wa' => '081234567890',
            'email' => 'verifikator@kemenag.go.id',
            'password' => 'passwordVerifikator',
            'wilayah_id' => 5,
        ]);

        $user = $this->userRepo->findById($id);
        $this->assertSame(Role::VERIFIKATOR, $user->getRole());
        $this->assertSame(StatusAkun::AKTIF, $user->getStatusAkun());

        $this->expectException(ValidationException::class);
        $service->createInternal([
            'role' => Role::SUPERADMIN,
            'nama_lengkap' => 'Super Admin Baru',
            'instansi' => 'IT',
            'nip_nik' => '198001012005011002',
            'no_wa' => '081234567891',
            'email' => 'superadmin2@kemenag.go.id',
            'password' => 'passwordSuperadmin',
            'wilayah_id' => 5,
        ]);
    }

    public function testCreateSuperadminCanAuthenticate(): void
    {
        $service = $this->createService();

        $id = $service->createSuperadmin([
            'nama_lengkap' => 'Super Admin Utama',
            'instansi' => 'Tim IT Kanwil',
            'nip_nik' => '197501012000011001',
            'no_wa' => '081122334455',
            'email' => 'admin.utama@kemenag.go.id',
            'password' => 'superSecretPassword',
            'wilayah_id' => 5,
        ]);

        $user = $this->userRepo->findById($id);
        $this->assertSame(Role::SUPERADMIN, $user->getRole());
        $this->assertSame(StatusAkun::AKTIF, $user->getStatusAkun());

        $authenticated = $service->authenticate('admin.utama@kemenag.go.id', 'superSecretPassword');
        $this->assertSame($id, $authenticated->getId());
    }
}
