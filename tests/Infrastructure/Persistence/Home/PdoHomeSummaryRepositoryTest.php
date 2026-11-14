<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Home;

use App\Domain\Home\PublicCondusivityStatus;
use App\Infrastructure\Persistence\Home\PdoHomeSummaryRepository;
use PDO;
use PDOException;
use PDOStatement;
use Prophecy\Argument;
use Psr\Container\ContainerInterface;
use Tests\TestCase;

class PdoHomeSummaryRepositoryTest extends TestCase
{
    public function testFallbackSummaryReturnedWhenDatabaseUnavailable(): void
    {
        $container = $this->prophesize(ContainerInterface::class);
        $container->get(PDO::class)
            ->willThrow(new PDOException('Connection refused'))
            ->shouldBeCalledOnce();

        /** @var ContainerInterface $containerInstance */
        $containerInstance = $container->reveal();
        $repo = new PdoHomeSummaryRepository($containerInstance);

        $kabupaten = $repo->getKabupatenCondusivitySummary();
        $agenda = $repo->getUpcomingAgendaTicker(5);
        $media = $repo->getMediaHubHighlights(4);

        $this->assertCount(5, $kabupaten);
        $this->assertNotEmpty($agenda);
        $this->assertNotEmpty($media);
        $this->assertSame('Kabupaten Malinau', $kabupaten[0]['nama_kabupaten']);
        $this->assertSame('Kondusif & Harmonis', $kabupaten[0]['label_publik']);
    }

    public function testDatabaseAggregationMapsVerifiedStatusAndPreservesNeutralLabels(): void
    {
        $stmt = $this->prophesize(PDOStatement::class);
        $stmt->fetch()->willReturn(
            [
                'nama_kabupaten' => 'Kabupaten Malinau',
                'jumlah_kecamatan' => 15,
                'total_laporan_valid' => 3,
                'active_merah' => 1,
                'active_kuning' => 1,
            ],
            [
                'nama_kabupaten' => 'Kabupaten Bulungan',
                'jumlah_kecamatan' => 10,
                'total_laporan_valid' => 2,
                'active_merah' => 0,
                'active_kuning' => 1,
            ],
            false
        );

        $pdo = $this->prophesize(PDO::class);
        $pdo->query(Argument::containingString("status_laporan IN ('diverifikasi', 'proses_mediasi', 'selesai')"))
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();

        $container = $this->prophesize(ContainerInterface::class);
        $container->get(PDO::class)->willReturn($pdo->reveal());

        /** @var ContainerInterface $containerInstance */
        $containerInstance = $container->reveal();
        $repo = new PdoHomeSummaryRepository($containerInstance);

        $summary = $repo->getKabupatenCondusivitySummary();

        $this->assertSame(PublicCondusivityStatus::MERAH, $summary[0]['status_kerawanan']);
        $this->assertSame('Dalam Koordinasi Intensif Lintas Sektor', $summary[0]['label_publik']);
        $this->assertSame(3, $summary[0]['total_laporan_valid']);

        $this->assertSame(PublicCondusivityStatus::KUNING, $summary[1]['status_kerawanan']);
        $this->assertSame('Dalam Fasilitasi Dialog & Pembinaan', $summary[1]['label_publik']);
        $this->assertSame(2, $summary[1]['total_laporan_valid']);

        $this->assertSame(PublicCondusivityStatus::HIJAU, $summary[2]['status_kerawanan']);
        $this->assertSame('Kondusif & Harmonis', $summary[2]['label_publik']);
    }
}
