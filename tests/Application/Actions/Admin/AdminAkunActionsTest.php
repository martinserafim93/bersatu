<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Admin;

use App\Application\Auth\AuthSession;
use App\Domain\User\Role;
use App\Domain\User\StatusAkun;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Domain\Wilayah\Wilayah;
use App\Domain\Wilayah\WilayahRepository;
use App\Infrastructure\Security\IdentityCipher;
use DI\Container;
use Psr\Http\Message\ServerRequestInterface as Request;
use Tests\Support\FakeUserRepository;
use Tests\TestCase;

class AdminAkunActionsTest extends TestCase
{
    private FakeUserRepository $userRepo;
    private IdentityCipher $cipher;
    private int $superadminId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepo = new FakeUserRepository();
        $this->cipher = new IdentityCipher(str_repeat('ab', 32));

        $admin = new User(
            1,
            'Super Administrator',
            'Kanwil Kemenag Kaltara',
            'superadmin@kaltara.kemenag.go.id',
            password_hash('SuperSecret123', PASSWORD_DEFAULT),
            Role::SUPERADMIN,
            1,
            StatusAkun::AKTIF,
            $this->cipher->encrypt('198001012005011001'),
            $this->cipher->encrypt('6281234567890'),
            '2026-11-09 08:00:00'
        );
        $this->userRepo->addUserDirectly($admin, $this->cipher->blindIndex('198001012005011001'));
        $this->superadminId = 1;
    }

    private function configureContainer(Container $container): void
    {
        $wilayahProphecy = $this->prophesize(WilayahRepository::class);
        $mockWilayah = new Wilayah(5, '65.01.05', 'Bulungan', 'Tanjung Palas Barat', 2.8, 117.3);
        $wilayahProphecy->findById(5)->willReturn($mockWilayah);
        $wilayahProphecy->findById(1)->willReturn($mockWilayah);

        /** @var WilayahRepository $wilayahRepo */
        $wilayahRepo = $wilayahProphecy->reveal();

        $container->set(UserRepository::class, $this->userRepo);
        $container->set(WilayahRepository::class, $wilayahRepo);
        $container->set(IdentityCipher::class, $this->cipher);
    }

    private function createAdminSession(int $userId): AuthSession
    {
        $sessionData = ['user_id' => $userId];
        return new AuthSession($sessionData, fn () => null, fn () => null);
    }

    private function createAdminRequest(
        string $method,
        string $path,
        ?array $body = null,
        int $userId = 1
    ): Request {
        $headers = [
            'HTTP_ACCEPT' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $request = $this->createRequest($method, $path, $headers)
            ->withAttribute('session', $this->createAdminSession($userId));

        if ($body !== null) {
            $request = $request->withParsedBody($body);
        }

        return $request;
    }

    public function testListAccountsReturnsAllWithDecryptedNipAndWa(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $applicant = new User(
            2,
            'Ahmad Penyuluh',
            'IPARI Malinau',
            'ahmad@example.com',
            'hash',
            Role::PENYULUH_IPARI,
            5,
            StatusAkun::MENUNGGU_VERIFIKASI,
            $this->cipher->encrypt('198501012010011002'),
            $this->cipher->encrypt('6281112223334'),
            '2026-11-09 09:00:00'
        );
        $this->userRepo->addUserDirectly($applicant, $this->cipher->blindIndex('198501012010011002'));

        $request = $this->createAdminRequest('GET', '/api/admin/akun');
        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $payload = json_decode((string) $response->getBody(), true);
        $this->assertCount(2, $payload['data']['akun']);

        $applicantData = null;
        foreach ($payload['data']['akun'] as $item) {
            if ($item['id'] === 2) {
                $applicantData = $item;
                break;
            }
        }

        $this->assertNotNull($applicantData);
        $this->assertSame('198501012010011002', $applicantData['nip_nik']);
        $this->assertSame('6281112223334', $applicantData['no_wa']);
        $this->assertSame(StatusAkun::MENUNGGU_VERIFIKASI, $applicantData['status_akun']);
    }

    public function testListAccountsFilteredByStatus(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $applicant = new User(
            2,
            'Budi Santoso',
            null,
            'budi@example.com',
            'hash',
            Role::MASYARAKAT,
            5,
            StatusAkun::MENUNGGU_VERIFIKASI,
            $this->cipher->encrypt('6501010101900001'),
            $this->cipher->encrypt('6285211112222'),
            '2026-11-09 09:00:00'
        );
        $this->userRepo->addUserDirectly($applicant, $this->cipher->blindIndex('6501010101900001'));

        $request = $this->createAdminRequest('GET', '/api/admin/akun')
            ->withQueryParams(['status' => StatusAkun::MENUNGGU_VERIFIKASI]);

        $response = $app->handle($request);
        $this->assertSame(200, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertCount(1, $payload['data']['akun']);
        $this->assertSame(2, $payload['data']['akun'][0]['id']);
    }

    public function testListAccountsInvalidStatusReturns422(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $request = $this->createAdminRequest('GET', '/api/admin/akun')
            ->withQueryParams(['status' => 'status_palsu']);

        $response = $app->handle($request);
        $this->assertSame(422, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('status', $payload['data']['errors']);
    }

    public function testCreateInternalAccountSuccess(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $body = [
            'role' => Role::VERIFIKATOR,
            'nama_lengkap' => 'Verifikator Kanwil',
            'instansi' => 'Bimas Islam Kanwil',
            'nip_nik' => '199001012015011003',
            'no_wa' => '081299887766',
            'email' => 'verifikator1@kaltara.kemenag.go.id',
            'password' => 'PasswordKuat123',
            'wilayah_id' => 5,
        ];

        $request = $this->createAdminRequest('POST', '/api/admin/akun', $body);
        $response = $app->handle($request);

        $this->assertSame(201, $response->getStatusCode());
        $payload = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('id', $payload['data']);

        $createdUser = $this->userRepo->findById($payload['data']['id']);
        $this->assertSame(Role::VERIFIKATOR, $createdUser->getRole());
        $this->assertSame(StatusAkun::AKTIF, $createdUser->getStatusAkun());
    }

    public function testCreateInternalAccountWithSuperadminRoleReturns422(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $body = [
            'role' => Role::SUPERADMIN,
            'nama_lengkap' => 'Superadmin Baru',
            'instansi' => 'Kanwil',
            'nip_nik' => '199001012015011004',
            'no_wa' => '081299887766',
            'email' => 'admin2@kaltara.kemenag.go.id',
            'password' => 'PasswordKuat123',
            'wilayah_id' => 5,
        ];

        $request = $this->createAdminRequest('POST', '/api/admin/akun', $body);
        $response = $app->handle($request);

        $this->assertSame(422, $response->getStatusCode());
        $payload = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('role', $payload['data']['errors']);
    }

    public function testCreateInternalAccountInvalidRoleReturns422(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $body = [
            'role' => 'peran_tidak_dikenal',
            'nama_lengkap' => 'Warga Sipil',
            'nip_nik' => '6501010101900002',
            'no_wa' => '081299887766',
            'email' => 'warga@example.com',
            'password' => 'PasswordKuat123',
            'wilayah_id' => 5,
        ];

        $request = $this->createAdminRequest('POST', '/api/admin/akun', $body);
        $response = $app->handle($request);

        $this->assertSame(422, $response->getStatusCode());
        $payload = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('role', $payload['data']['errors']);
    }

    public function testCreateInternalAccountDuplicateEmailReturns409(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $body = [
            'role' => Role::PIMPINAN,
            'nama_lengkap' => 'Kakanwil Kaltara',
            'instansi' => 'Kanwil',
            'nip_nik' => '197001011995011001',
            'no_wa' => '081299887766',
            'email' => 'superadmin@kaltara.kemenag.go.id',
            'password' => 'PasswordKuat123',
            'wilayah_id' => 5,
        ];

        $request = $this->createAdminRequest('POST', '/api/admin/akun', $body);
        $response = $app->handle($request);

        $this->assertSame(409, $response->getStatusCode());
    }

    public function testApproveAccountSuccess(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $applicant = new User(
            2,
            'Ahmad Fauzi',
            'KUA Malinau',
            'ahmad@example.com',
            'hash',
            Role::KUA,
            5,
            StatusAkun::MENUNGGU_VERIFIKASI,
            'enc-nip',
            'enc-wa',
            '2026-11-09 09:00:00'
        );
        $this->userRepo->addUserDirectly($applicant, 'hash-nip');

        $request = $this->createAdminRequest('POST', '/api/admin/akun/2/setujui');
        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $payload = json_decode((string) $response->getBody(), true);
        $this->assertSame(2, $payload['data']['id']);
        $this->assertSame(StatusAkun::AKTIF, $payload['data']['status_akun']);

        $updated = $this->userRepo->findById(2);
        $this->assertSame(StatusAkun::AKTIF, $updated->getStatusAkun());
    }

    public function testApproveAccountWithRoleCorrectionSuccess(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $applicant = new User(
            2,
            'Ahmad Fauzi',
            'KUA Malinau',
            'ahmad@example.com',
            'hash',
            Role::PENYULUH_IPARI,
            5,
            StatusAkun::MENUNGGU_VERIFIKASI,
            'enc-nip',
            'enc-wa',
            '2026-11-09 09:00:00'
        );
        $this->userRepo->addUserDirectly($applicant, 'hash-nip');

        $request = $this->createAdminRequest('POST', '/api/admin/akun/2/setujui', ['role' => Role::KUA]);
        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $updated = $this->userRepo->findById(2);
        $this->assertSame(Role::KUA, $updated->getRole());
        $this->assertSame(StatusAkun::AKTIF, $updated->getStatusAkun());
    }

    public function testApproveNonExistentIdReturns404(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $request = $this->createAdminRequest('POST', '/api/admin/akun/999/setujui');
        $response = $app->handle($request);

        $this->assertSame(404, $response->getStatusCode());
    }

    public function testApproveSuperadminReturns409(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $request = $this->createAdminRequest('POST', '/api/admin/akun/1/setujui');
        $response = $app->handle($request);

        $this->assertSame(409, $response->getStatusCode());
    }

    public function testApproveAlreadyActiveReturns409(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $user = new User(
            2,
            'Aktif User',
            'KUA',
            'aktif@example.com',
            'hash',
            Role::KUA,
            5,
            StatusAkun::AKTIF,
            'enc-nip',
            'enc-wa',
            '2026-11-09 09:00:00'
        );
        $this->userRepo->addUserDirectly($user, 'hash-nip');

        $request = $this->createAdminRequest('POST', '/api/admin/akun/2/setujui');
        $response = $app->handle($request);

        $this->assertSame(409, $response->getStatusCode());
    }

    public function testRejectAccountSuccess(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $applicant = new User(
            2,
            'Ahmad Fauzi',
            'KUA',
            'ahmad@example.com',
            'hash',
            Role::KUA,
            5,
            StatusAkun::MENUNGGU_VERIFIKASI,
            'enc-nip',
            'enc-wa',
            '2026-11-09 09:00:00'
        );
        $this->userRepo->addUserDirectly($applicant, 'hash-nip');

        $request = $this->createAdminRequest('POST', '/api/admin/akun/2/tolak');
        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $payload = json_decode((string) $response->getBody(), true);
        $this->assertSame(StatusAkun::DITOLAK, $payload['data']['status_akun']);

        $updated = $this->userRepo->findById(2);
        $this->assertSame(StatusAkun::DITOLAK, $updated->getStatusAkun());
    }

    public function testRejectAlreadyActiveReturns409(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $user = new User(
            2,
            'Aktif User',
            'KUA',
            'aktif@example.com',
            'hash',
            Role::KUA,
            5,
            StatusAkun::AKTIF,
            'enc-nip',
            'enc-wa',
            '2026-11-09 09:00:00'
        );
        $this->userRepo->addUserDirectly($user, 'hash-nip');

        $request = $this->createAdminRequest('POST', '/api/admin/akun/2/tolak');
        $response = $app->handle($request);

        $this->assertSame(409, $response->getStatusCode());
    }

    public function testDeactivateAccountSuccess(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $user = new User(
            2,
            'Aktif User',
            'KUA',
            'aktif@example.com',
            'hash',
            Role::KUA,
            5,
            StatusAkun::AKTIF,
            'enc-nip',
            'enc-wa',
            '2026-11-09 09:00:00'
        );
        $this->userRepo->addUserDirectly($user, 'hash-nip');

        $request = $this->createAdminRequest('POST', '/api/admin/akun/2/nonaktifkan');
        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $payload = json_decode((string) $response->getBody(), true);
        $this->assertSame(StatusAkun::NONAKTIF, $payload['data']['status_akun']);

        $updated = $this->userRepo->findById(2);
        $this->assertSame(StatusAkun::NONAKTIF, $updated->getStatusAkun());
    }

    public function testDeactivateAlreadyNonaktifReturns409(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $user = new User(
            2,
            'Nonaktif User',
            'KUA',
            'nonaktif@example.com',
            'hash',
            Role::KUA,
            5,
            StatusAkun::NONAKTIF,
            'enc-nip',
            'enc-wa',
            '2026-11-09 09:00:00'
        );
        $this->userRepo->addUserDirectly($user, 'hash-nip');

        $request = $this->createAdminRequest('POST', '/api/admin/akun/2/nonaktifkan');
        $response = $app->handle($request);

        $this->assertSame(409, $response->getStatusCode());
    }

    public function testReactivateAccountSuccess(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $user = new User(
            2,
            'Nonaktif User',
            'KUA',
            'nonaktif@example.com',
            'hash',
            Role::KUA,
            5,
            StatusAkun::NONAKTIF,
            'enc-nip',
            'enc-wa',
            '2026-11-09 09:00:00'
        );
        $this->userRepo->addUserDirectly($user, 'hash-nip');

        $request = $this->createAdminRequest('POST', '/api/admin/akun/2/aktifkan');
        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $payload = json_decode((string) $response->getBody(), true);
        $this->assertSame(StatusAkun::AKTIF, $payload['data']['status_akun']);

        $updated = $this->userRepo->findById(2);
        $this->assertSame(StatusAkun::AKTIF, $updated->getStatusAkun());
    }

    public function testReactivateAlreadyActiveReturns409(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $user = new User(
            2,
            'Aktif User',
            'KUA',
            'aktif@example.com',
            'hash',
            Role::KUA,
            5,
            StatusAkun::AKTIF,
            'enc-nip',
            'enc-wa',
            '2026-11-09 09:00:00'
        );
        $this->userRepo->addUserDirectly($user, 'hash-nip');

        $request = $this->createAdminRequest('POST', '/api/admin/akun/2/aktifkan');
        $response = $app->handle($request);

        $this->assertSame(409, $response->getStatusCode());
    }

    public function testAdminRoutesWithoutSessionReturns401(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $request = $this->createRequest('GET', '/api/admin/akun');
        $response = $app->handle($request);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testAdminRoutesWithNonSuperadminRolesReturns403(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $nonAdminRoles = [
            Role::VERIFIKATOR,
            Role::PIMPINAN,
            Role::MASYARAKAT,
            Role::PENYULUH_IPARI,
            Role::KUA,
            Role::FKUB,
        ];

        $currentId = 10;
        foreach ($nonAdminRoles as $role) {
            $currentId++;
            $user = new User(
                $currentId,
                'User ' . $role,
                'Instansi',
                'user' . $currentId . '@example.com',
                'hash',
                $role,
                5,
                StatusAkun::AKTIF,
                'enc-nip',
                'enc-wa',
                '2026-11-09 09:00:00'
            );
            $this->userRepo->addUserDirectly($user, 'hash-' . $currentId);

            $request = $this->createAdminRequest('GET', '/api/admin/akun', null, $currentId);
            $response = $app->handle($request);

            $this->assertSame(403, $response->getStatusCode(), "Role $role harus ditolak 403.");
        }
    }

    public function testAdminPostRoutesWithoutJsonContentTypeReturns415(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $request = $this->createRequest('POST', '/api/admin/akun/2/setujui', [
            'HTTP_ACCEPT' => 'application/json',
            'Content-Type' => 'application/x-www-form-urlencoded',
        ])->withAttribute('session', $this->createAdminSession($this->superadminId));

        $response = $app->handle($request);
        $this->assertSame(415, $response->getStatusCode());
    }

    public function testEndToEndPublicRegisterApproveLoginDeactivateMeFlow(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        // 1. Register public user
        $regData = [
            'role' => Role::KUA,
            'nama_lengkap' => 'Kepala KUA Baru',
            'instansi' => 'KUA Malinau Barat',
            'nip_nik' => '198505052010011005',
            'no_wa' => '085200001111',
            'email' => 'kepalakua@kaltara.kemenag.go.id',
            'password' => 'KUASecret123',
            'wilayah_id' => 5,
        ];

        $regReq = $this->createRequest('POST', '/api/auth/register', [
            'HTTP_ACCEPT' => 'application/json',
            'Content-Type' => 'application/json',
        ])->withParsedBody($regData);

        $regRes = $app->handle($regReq);
        $this->assertSame(201, $regRes->getStatusCode());
        $regPayload = json_decode((string) $regRes->getBody(), true);
        $newUserId = $regPayload['data']['id'];
        $this->assertSame(StatusAkun::MENUNGGU_VERIFIKASI, $regPayload['data']['status_akun']);

        // 2. Attempt login before approval -> 403 VERIFICATION_ERROR
        $loginSessionData = [];
        $loginSession = new AuthSession($loginSessionData, fn () => null, fn () => null);

        $loginReq = $this->createRequest('POST', '/api/auth/login', [
            'HTTP_ACCEPT' => 'application/json',
            'Content-Type' => 'application/json',
        ])->withAttribute('session', $loginSession)
          ->withParsedBody([
              'email' => 'kepalakua@kaltara.kemenag.go.id',
              'password' => 'KUASecret123',
          ]);

        $loginRes = $app->handle($loginReq);
        $this->assertSame(403, $loginRes->getStatusCode());
        $loginPayload = json_decode((string) $loginRes->getBody(), true);
        $this->assertSame('VERIFICATION_ERROR', $loginPayload['error']['type']);

        // 3. Superadmin approves the account
        $approveReq = $this->createAdminRequest('POST', '/api/admin/akun/' . $newUserId . '/setujui');
        $approveRes = $app->handle($approveReq);
        $this->assertSame(200, $approveRes->getStatusCode());

        // 4. Login after approval succeeds
        $postApproveSessionData = [];
        $postApproveSession = new AuthSession($postApproveSessionData, fn () => null, fn () => null);

        $loginSuccessReq = $this->createRequest('POST', '/api/auth/login', [
            'HTTP_ACCEPT' => 'application/json',
            'Content-Type' => 'application/json',
        ])->withAttribute('session', $postApproveSession)
          ->withParsedBody([
              'email' => 'kepalakua@kaltara.kemenag.go.id',
              'password' => 'KUASecret123',
          ]);

        $loginSuccessRes = $app->handle($loginSuccessReq);
        $this->assertSame(200, $loginSuccessRes->getStatusCode());
        $this->assertSame($newUserId, $postApproveSession->userId());

        // 5. GET /api/auth/me succeeds
        $meReq = $this->createRequest('GET', '/api/auth/me')
            ->withAttribute('session', $postApproveSession);

        $meRes = $app->handle($meReq);
        $this->assertSame(200, $meRes->getStatusCode());

        // 6. Superadmin deactivates the user
        $deactivateReq = $this->createAdminRequest('POST', '/api/admin/akun/' . $newUserId . '/nonaktifkan');
        $deactivateRes = $app->handle($deactivateReq);
        $this->assertSame(200, $deactivateRes->getStatusCode());

        // 7. GET /api/auth/me with old session now returns 401
        $meAfterDeactivateReq = $this->createRequest('GET', '/api/auth/me')
            ->withAttribute('session', $postApproveSession);

        $meAfterDeactivateRes = $app->handle($meAfterDeactivateReq);
        $this->assertSame(401, $meAfterDeactivateRes->getStatusCode());
        $this->assertNull($postApproveSession->userId());
    }
}
