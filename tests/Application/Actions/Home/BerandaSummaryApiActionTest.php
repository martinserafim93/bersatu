<?php

declare(strict_types=1);

namespace Tests\Application\Actions\Home;

use App\Application\Actions\ActionPayload;
use App\Domain\Home\HomeSummaryRepository;
use App\Domain\Home\PublicCondusivityStatus;
use DI\Container;
use Tests\TestCase;

class BerandaSummaryApiActionTest extends TestCase
{
    public function testGetBerandaSummaryReturnsExpectedPublicContract(): void
    {
        $app = $this->getAppInstance();

        /** @var Container $container */
        $container = $app->getContainer();

        $kabupatenSummary = PublicCondusivityStatus::buildDefaultKabupatenSummary();
        $kabupatenSummary[0]['status_kerawanan'] = PublicCondusivityStatus::KUNING;
        $kabupatenSummary[0]['label_publik'] = PublicCondusivityStatus::publicLabel(PublicCondusivityStatus::KUNING);
        $kabupatenSummary[0]['total_laporan_valid'] = 2;

        $agenda = [
            [
                'id' => 10,
                'judul_agenda' => 'Dialog Lintas Iman Malinau',
                'lokasi_tempat' => 'Sekretariat FKUB Malinau',
                'nama_kabupaten' => 'Kabupaten Malinau',
                'tanggal_mulai' => '2026-06-01 09:00:00',
            ],
        ];

        $highlights = [
            [
                'id' => 5,
                'judul' => 'Suara Harmoni Perbatasan Episode 1',
                'tipe_konten' => 'podcast',
                'agama' => 'semua_agama',
                'tokoh_narasumber' => 'Penyuluh Agama Malinau',
            ],
        ];

        $repo = $this->prophesize(HomeSummaryRepository::class);
        $repo->getKabupatenCondusivitySummary()->willReturn($kabupatenSummary)->shouldBeCalledOnce();
        $repo->getUpcomingAgendaTicker(5)->willReturn($agenda)->shouldBeCalledOnce();
        $repo->getMediaHubHighlights(4)->willReturn($highlights)->shouldBeCalledOnce();

        $container->set(HomeSummaryRepository::class, $repo->reveal());

        $request = $this->createRequest('GET', '/api/public/beranda');
        $response = $app->handle($request);

        $this->assertSame(200, $response->getStatusCode());

        $expectedPayload = new ActionPayload(200, [
            'provinsi' => 'Kalimantan Utara',
            'peringkat_ikub_nasional' => 1,
            'tahun_ikub' => 2025,
            'total_kabupaten_kota' => 5,
            'total_kecamatan' => 55,
            'lokus_piloting' => 'Kabupaten Malinau',
            'kabupaten_summary' => $kabupatenSummary,
            'agenda_ticker' => $agenda,
            'media_highlights' => $highlights,
        ]);

        $this->assertSame(
            json_encode($expectedPayload, JSON_PRETTY_PRINT),
            (string) $response->getBody()
        );
    }
}
