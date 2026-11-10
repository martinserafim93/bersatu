<?php

declare(strict_types=1);

namespace Tests\Domain\User;

use App\Domain\User\AccountService;
use App\Domain\User\DuplicateUserException;
use App\Domain\User\Role;
use App\Domain\User\StatusAkun;
use App\Domain\User\ValidationException;
use App\Domain\Wilayah\Wilayah;
use App\Domain\Wilayah\WilayahNotFoundException;
use App\Domain\Wilayah\WilayahRepository;
use App\Infrastructure\Security\IdentityCipher;
use Tests\Support\FakeUserRepository;
use Tests\TestCase;

class AccountServiceRegisterTest extends TestCase
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

    /**
     * @return WilayahRepository
     */
    private function createWilayahRepositoryProphecy(): WilayahRepository
    {
        $prophecy = $this->prophesize(WilayahRepository::class);
        $prophecy->findById(5)->willReturn($this->mockWilayah);
        $prophecy->findById(999)->willThrow(new WilayahNotFoundException());

        /** @var WilayahRepository $instance */
        $instance = $prophecy->reveal();
        return $instance;
    }

    public function testRegisterMasyarakatSuccess(): void
    {
        $service = new AccountService($this->userRepo, $this->createWilayahRepositoryProphecy(), $this->cipher);

        $id = $service->register([
            'role' => Role::MASYARAKAT,
            'nama_lengkap' => 'Budi Santoso',
            'instansi' => 'Abaikan Ini',
            'nip_nik' => '6501051234560001',
            'no_wa' => '0812-3456-7890',
            'email' => 'Budi.Santoso@Example.COM',
            'password' => 'rahasia123',
            'wilayah_id' => 5,
        ]);

        $this->assertSame(1, $id);
        $user = $this->userRepo->findById(1);

        $this->assertSame(Role::MASYARAKAT, $user->getRole());
        $this->assertSame(StatusAkun::MENUNGGU_VERIFIKASI, $user->getStatusAkun());
        $this->assertSame('budi.santoso@example.com', $user->getEmail());
        $this->assertNull($user->getInstansi());
        $this->assertTrue(password_verify('rahasia123', $user->getPasswordHash()));

        $this->assertNotSame('6501051234560001', $user->getNipNikEncrypted());
        $this->assertSame('6501051234560001', $this->cipher->decrypt($user->getNipNikEncrypted()));

        $this->assertNotSame('6281234567890', $user->getNoWaEncrypted());
        $this->assertSame('6281234567890', $this->cipher->decrypt($user->getNoWaEncrypted()));
    }

    public function testRegisterFieldReportersSuccess(): void
    {
        $service = new AccountService($this->userRepo, $this->createWilayahRepositoryProphecy(), $this->cipher);

        $roles = [Role::PENYULUH_IPARI, Role::KUA, Role::FKUB];
        foreach ($roles as $idx => $role) {
            $id = $service->register([
                'role' => $role,
                'nama_lengkap' => 'Petugas ' . $role,
                'instansi' => 'Kantor ' . $role,
                'nip_nik' => '19850101201001100' . ($idx + 1),
                'no_wa' => '+62 813-9876-543' . $idx,
                'email' => "petugas{$idx}@kemenag.go.id",
                'password' => 'passwordPetugas',
                'wilayah_id' => 5,
            ]);

            $user = $this->userRepo->findById($id);
            $this->assertSame($role, $user->getRole());
            $this->assertSame('Kantor ' . $role, $user->getInstansi());
            $this->assertSame(StatusAkun::MENUNGGU_VERIFIKASI, $user->getStatusAkun());
            $this->assertSame('19850101201001100' . ($idx + 1), $this->cipher->decrypt($user->getNipNikEncrypted()));
        }
    }

    public function testRegisterDuplicateThrowsDuplicateUserException(): void
    {
        $service = new AccountService($this->userRepo, $this->createWilayahRepositoryProphecy(), $this->cipher);

        $service->register([
            'role' => Role::MASYARAKAT,
            'nama_lengkap' => 'Warga Satu',
            'nip_nik' => '6501051234560001',
            'no_wa' => '081234567890',
            'email' => 'warga@example.com',
            'password' => 'password123',
            'wilayah_id' => 5,
        ]);

        $this->expectException(DuplicateUserException::class);
        $service->register([
            'role' => Role::MASYARAKAT,
            'nama_lengkap' => 'Warga Dua',
            'nip_nik' => '6501051234560001',
            'no_wa' => '081234567899',
            'email' => 'warga.beda@example.com',
            'password' => 'password123',
            'wilayah_id' => 5,
        ]);
    }

    public function testValidationCollectsAllErrors(): void
    {
        $service = new AccountService($this->userRepo, $this->createWilayahRepositoryProphecy(), $this->cipher);

        try {
            $service->register([
                'role' => 'superadmin',
                'nama_lengkap' => '',
                'nip_nik' => 'abc',
                'no_wa' => '123',
                'email' => 'bukan-email',
                'password' => 'pendek',
                'wilayah_id' => 999,
            ]);
            $this->fail('Harus melempar ValidationException');
        } catch (ValidationException $e) {
            $errors = $e->getErrors();
            $this->assertArrayHasKey('role', $errors);
            $this->assertSame('Peran tidak valid.', $errors['role']);
            $this->assertArrayHasKey('nama_lengkap', $errors);
            $this->assertArrayHasKey('nip_nik', $errors);
            $this->assertArrayHasKey('no_wa', $errors);
            $this->assertArrayHasKey('email', $errors);
            $this->assertArrayHasKey('password', $errors);
            $this->assertArrayHasKey('wilayah_id', $errors);
            $this->assertSame('Wilayah tidak ditemukan.', $errors['wilayah_id']);
        }
    }

    public function testFieldReporterRequiresInstansi(): void
    {
        $service = new AccountService($this->userRepo, $this->createWilayahRepositoryProphecy(), $this->cipher);

        try {
            $service->register([
                'role' => Role::KUA,
                'nama_lengkap' => 'Kepala KUA',
                'instansi' => '',
                'nip_nik' => '198501012010011001',
                'no_wa' => '081234567890',
                'email' => 'kua@example.com',
                'password' => 'password123',
                'wilayah_id' => 5,
            ]);
            $this->fail('Harus melempar ValidationException');
        } catch (ValidationException $e) {
            $this->assertSame(
                'Instansi/lembaga wajib diisi (maksimal 150 karakter).',
                $e->getErrors()['instansi'] ?? null
            );
        }
    }

    public function testMasyarakatRejects18DigitNip(): void
    {
        $service = new AccountService($this->userRepo, $this->createWilayahRepositoryProphecy(), $this->cipher);

        try {
            $service->register([
                'role' => Role::MASYARAKAT,
                'nama_lengkap' => 'Warga Biasa',
                'nip_nik' => '198501012010011001',
                'no_wa' => '081234567890',
                'email' => 'warga@example.com',
                'password' => 'password123',
                'wilayah_id' => 5,
            ]);
            $this->fail('Harus melempar ValidationException');
        } catch (ValidationException $e) {
            $this->assertSame('NIK harus 16 digit angka.', $e->getErrors()['nip_nik'] ?? null);
        }
    }
}
