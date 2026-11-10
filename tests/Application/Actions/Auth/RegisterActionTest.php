<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Auth;

use App\Domain\User\Role;
use App\Domain\User\StatusAkun;
use App\Domain\User\UserRepository;
use App\Domain\Wilayah\Wilayah;
use App\Domain\Wilayah\WilayahRepository;
use App\Infrastructure\Security\IdentityCipher;
use DI\Container;
use Tests\Support\FakeUserRepository;
use Tests\TestCase;

class RegisterActionTest extends TestCase
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
        $wilayahProphecy = $this->prophesize(WilayahRepository::class);
        $wilayahProphecy->findById(5)->willReturn(new Wilayah(
            5,
            '65.01.05',
            'Kabupaten Bulungan',
            'Tanjung Selor',
            2.8375,
            117.365278,
            null
        ));

        $container->set(UserRepository::class, $this->userRepo);
        $container->set(WilayahRepository::class, $wilayahProphecy->reveal());
        $container->set(IdentityCipher::class, $this->cipher);
    }

    public function testRegisterSuccessReturns201AndPendingStatus(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $body = [
            'role' => Role::MASYARAKAT,
            'nama_lengkap' => 'Warga Kaltara',
            'nip_nik' => '6501051234560001',
            'no_wa' => '081234567890',
            'email' => 'warga@kaltara.go.id',
            'password' => 'password123',
            'wilayah_id' => 5,
        ];

        $request = $this->createRequest('POST', '/api/auth/register', [
            'HTTP_ACCEPT' => 'application/json',
            'Content-Type' => 'application/json',
        ])->withParsedBody($body);

        $response = $app->handle($request);
        $this->assertSame(201, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertSame(201, $payload['statusCode']);
        $this->assertSame(1, $payload['data']['id']);
        $this->assertSame(StatusAkun::MENUNGGU_VERIFIKASI, $payload['data']['status_akun']);
    }

    public function testRegisterValidationFailureReturns422WithErrors(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $request = $this->createRequest('POST', '/api/auth/register', [
            'HTTP_ACCEPT' => 'application/json',
            'Content-Type' => 'application/json',
        ])->withParsedBody([
            'role' => Role::SUPERADMIN,
            'nama_lengkap' => '',
        ]);

        $response = $app->handle($request);
        $this->assertSame(422, $response->getStatusCode());

        $payload = json_decode((string) $response->getBody(), true);
        $this->assertSame(422, $payload['statusCode']);
        $this->assertArrayHasKey('errors', $payload['data']);
        $this->assertSame('Peran tidak valid.', $payload['data']['errors']['role']);
    }

    public function testRegisterDuplicateReturns409(): void
    {
        $app = $this->getAppInstance();
        /** @var Container $container */
        $container = $app->getContainer();
        $this->configureContainer($container);

        $body = [
            'role' => Role::MASYARAKAT,
            'nama_lengkap' => 'Warga Kaltara',
            'nip_nik' => '6501051234560001',
            'no_wa' => '081234567890',
            'email' => 'warga@kaltara.go.id',
            'password' => 'password123',
            'wilayah_id' => 5,
        ];

        $request1 = $this->createRequest('POST', '/api/auth/register', [
            'HTTP_ACCEPT' => 'application/json',
            'Content-Type' => 'application/json',
        ])->withParsedBody($body);
        $app->handle($request1);

        $request2 = $this->createRequest('POST', '/api/auth/register', [
            'HTTP_ACCEPT' => 'application/json',
            'Content-Type' => 'application/json',
        ])->withParsedBody($body);

        $response2 = $app->handle($request2);
        $this->assertSame(409, $response2->getStatusCode());

        $payload = json_decode((string) $response2->getBody(), true);
        $this->assertSame('NOT_ALLOWED', $payload['error']['type']);
    }

    public function testRegisterWithoutJsonContentTypeReturns415(): void
    {
        $app = $this->getAppInstance();

        $request = $this->createRequest('POST', '/api/auth/register', [
            'HTTP_ACCEPT' => 'application/json',
        ])->withParsedBody(['dummy' => 'val']);

        $response = $app->handle($request);
        $this->assertSame(415, $response->getStatusCode());
    }
}
