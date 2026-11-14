<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Home;

use App\Application\Auth\AuthSession;
use App\Domain\Home\HomeSummaryRepository;
use App\Domain\Home\PublicCondusivityStatus;
use App\Domain\User\Role;
use App\Domain\User\StatusAkun;
use App\Domain\User\User;
use App\Domain\User\UserRepository;
use App\Domain\Wilayah\Wilayah;
use App\Domain\Wilayah\WilayahRepository;
use DI\Container;
use Tests\Support\FakeUserRepository;
use Tests\TestCase;

class HomePageActionTest extends TestCase
{
    public function testHomePageRendersFiveRegionsNeutralLabelsAndPwaManifest(): void
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $summary = PublicCondusivityStatus::buildDefaultKabupatenSummary();
        $summary[1]['status_kerawanan'] = PublicCondusivityStatus::KUNING;
        $summary[1]['label_publik'] = PublicCondusivityStatus::publicLabel(PublicCondusivityStatus::KUNING);
        $summary[1]['total_laporan_valid'] = 1;

        $repo = $this->prophesize(HomeSummaryRepository::class);
        $repo->getKabupatenCondusivitySummary()->willReturn($summary)->shouldBeCalledOnce();
        $repo->getUpcomingAgendaTicker(5)->willReturn([
            [
                'id' => 1,
                'judul_agenda' => 'Dialog Lintas Iman Malinau',
                'lokasi_tempat' => 'FKUB Malinau',
                'nama_kabupaten' => 'Kabupaten Malinau',
                'tanggal_mulai' => '2026-06-15 09:00:00',
            ],
        ])->shouldBeCalledOnce();
        $repo->getMediaHubHighlights(4)->willReturn([
            [
                'id' => 1,
                'judul' => 'Suara Harmoni Perbatasan: Ekoteologi Kaltara',
                'tipe_konten' => 'podcast',
                'agama' => 'semua_agama',
                'tokoh_narasumber' => 'Tim Penyuluh Malinau',
            ],
        ])->shouldBeCalledOnce();

        $container->set(HomeSummaryRepository::class, $repo->reveal());

        $request = $this->createRequest('GET', '/', ['HTTP_ACCEPT' => 'text/html']);
        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));

        $body = (string) $response->getBody();
        $this->assertStringContainsString('href="/manifest.json"', $body);
        $this->assertStringContainsString('Portal BERSATU', $body);
        $this->assertStringContainsString('Kabupaten Malinau', $body);
        $this->assertStringContainsString('Kabupaten Bulungan', $body);
        $this->assertStringContainsString('Kota Tarakan', $body);
        $this->assertStringContainsString('Kabupaten Nunukan', $body);
        $this->assertStringContainsString('Kabupaten Tana Tidung', $body);
        $this->assertStringContainsString('Kondusif &amp; Harmonis', $body);
        $this->assertStringContainsString('Dalam Fasilitasi Dialog &amp; Pembinaan', $body);
        $this->assertStringContainsString('Dialog Lintas Iman Malinau', $body);
        $this->assertStringContainsString('Lapor Dini EWS', $body);
        $this->assertStringNotContainsString('Ancaman Konflik Mendesak', $body);
        $this->assertStringNotContainsString('Potensi Ketegangan', $body);
    }

    public function testHomePageAsSuperadminHidesEwsReportActionAndShowsAdminPanel(): void
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $userRepo = new FakeUserRepository();
        $superadmin = new User(
            7,
            'Admin Kanwil',
            'Kanwil Kemenag Kaltara',
            'admin@kaltara.kemenag.go.id',
            'hash',
            Role::SUPERADMIN,
            5,
            StatusAkun::AKTIF,
            'enc',
            'enc',
            '2026-11-10 09:00:00'
        );
        $userRepo->addUserDirectly($superadmin, 'blindhash');

        $summaryRepo = $this->prophesize(HomeSummaryRepository::class);
        $summaryRepo->getKabupatenCondusivitySummary()
            ->willReturn(PublicCondusivityStatus::buildDefaultKabupatenSummary());
        $summaryRepo->getUpcomingAgendaTicker(5)->willReturn([]);
        $summaryRepo->getMediaHubHighlights(4)->willReturn([]);

        $container->set(UserRepository::class, $userRepo);
        $container->set(HomeSummaryRepository::class, $summaryRepo->reveal());

        $sessionData = ['user_id' => 7];
        $session = new AuthSession($sessionData, fn () => null, fn () => null);

        $request = $this->createRequest('GET', '/', ['HTTP_ACCEPT' => 'text/html'])
            ->withAttribute('session', $session);
        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Admin Kanwil', $body);
        $this->assertStringContainsString('/admin', $body);
        $this->assertStringNotContainsString('href="/ews/lapor"', $body);
    }

    public function testOfflinePageRenders200Html(): void
    {
        $app = $this->getAppInstance();
        $request = $this->createRequest('GET', '/offline', ['HTTP_ACCEPT' => 'text/html']);
        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('Perangkat Sedang Offline', $body);
        $this->assertStringContainsString('Buka Beranda Tersimpan', $body);
    }

    public function testLoginPageRendersFormPostingToLoginApi(): void
    {
        $app = $this->getAppInstance();
        $request = $this->createRequest('GET', '/auth/login', ['HTTP_ACCEPT' => 'text/html']);
        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('id="login-form"', $body);
        $this->assertStringContainsString('action="/api/auth/login"', $body);
        $this->assertStringContainsString('Menunggu Verifikasi', $body);
    }

    public function testRegisterPageRendersWilayahOptionsAndPublicRoles(): void
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $wilayahRepo = $this->prophesize(WilayahRepository::class);
        $wilayahRepo->findAll(false)->willReturn([
            new Wilayah(12, '65.02.02', 'Kabupaten Malinau', 'Malinau Kota', 3.58, 116.63),
        ])->shouldBeCalledOnce();

        $container->set(WilayahRepository::class, $wilayahRepo->reveal());

        $request = $this->createRequest('GET', '/auth/register', ['HTTP_ACCEPT' => 'text/html']);
        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $body = (string) $response->getBody();
        $this->assertStringContainsString('id="register-form"', $body);
        $this->assertStringContainsString('action="/api/auth/register"', $body);
        $this->assertStringContainsString('Kabupaten Malinau - Malinau Kota', $body);
        $this->assertStringContainsString('value="masyarakat"', $body);
        $this->assertStringContainsString('value="penyuluh_ipari"', $body);
        $this->assertStringContainsString('value="kua"', $body);
        $this->assertStringContainsString('value="fkub"', $body);
        $this->assertStringNotContainsString('value="superadmin"', $body);
    }
}
