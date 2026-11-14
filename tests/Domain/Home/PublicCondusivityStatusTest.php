<?php

declare(strict_types=1);

namespace Tests\Domain\Home;

use App\Domain\Home\PublicCondusivityStatus;
use Tests\TestCase;

class PublicCondusivityStatusTest extends TestCase
{
    public function testPublicLabelsUseNeutralNonPanicLanguage(): void
    {
        $hijau = PublicCondusivityStatus::publicLabel(PublicCondusivityStatus::HIJAU);
        $kuning = PublicCondusivityStatus::publicLabel(PublicCondusivityStatus::KUNING);
        $merah = PublicCondusivityStatus::publicLabel(PublicCondusivityStatus::MERAH);

        $this->assertSame('Kondusif & Harmonis', $hijau);
        $this->assertSame('Dalam Fasilitasi Dialog & Pembinaan', $kuning);
        $this->assertSame('Dalam Koordinasi Intensif Lintas Sektor', $merah);

        foreach ([$hijau, $kuning, $merah] as $label) {
            $this->assertStringNotContainsString('Ancaman Konflik Mendesak', $label);
            $this->assertStringNotContainsString('Potensi Ketegangan', $label);
        }
    }

    public function testResolveKabupatenStatusPrioritizesMerahOverKuningAndHijau(): void
    {
        $this->assertSame(
            PublicCondusivityStatus::MERAH,
            PublicCondusivityStatus::resolveKabupatenStatus(1, 2)
        );
        $this->assertSame(
            PublicCondusivityStatus::KUNING,
            PublicCondusivityStatus::resolveKabupatenStatus(0, 3)
        );
        $this->assertSame(
            PublicCondusivityStatus::HIJAU,
            PublicCondusivityStatus::resolveKabupatenStatus(0, 0)
        );
    }

    public function testBuildDefaultKabupatenSummaryCoversAllFiveRegionsAnd55Kecamatan(): void
    {
        $summary = PublicCondusivityStatus::buildDefaultKabupatenSummary();

        $this->assertCount(5, $summary);

        $totalKecamatan = array_sum(array_column($summary, 'jumlah_kecamatan'));
        $this->assertSame(55, $totalKecamatan);

        $names = array_column($summary, 'nama_kabupaten');
        $this->assertContains('Kabupaten Malinau', $names);
        $this->assertContains('Kabupaten Bulungan', $names);
        $this->assertContains('Kota Tarakan', $names);
        $this->assertContains('Kabupaten Nunukan', $names);
        $this->assertContains('Kabupaten Tana Tidung', $names);

        $malinau = null;
        foreach ($summary as $item) {
            if ($item['nama_kabupaten'] === 'Kabupaten Malinau') {
                $malinau = $item;
            }
            $this->assertSame('Kondusif & Harmonis', $item['label_publik']);
            $this->assertSame(0, $item['total_laporan_valid']);
        }

        $this->assertNotNull($malinau);
        $this->assertTrue($malinau['is_piloting']);
        $this->assertSame(15, $malinau['jumlah_kecamatan']);
    }
}
