<?php

declare(strict_types=1);

namespace Tests\Domain\Wilayah;

use App\Domain\Wilayah\Wilayah;
use Tests\TestCase;

class WilayahTest extends TestCase
{
    public function testGettersAndSerialization(): void
    {
        $polygon = '{"type":"Feature"}';
        $wilayah = new Wilayah(
            1,
            '65.01.01',
            'Kabupaten Bulungan',
            'Tanjung Palas',
            2.83333300,
            117.35000000,
            $polygon
        );

        $this->assertSame(1, $wilayah->getId());
        $this->assertSame('65.01.01', $wilayah->getKodeWilayah());
        $this->assertSame('Kabupaten Bulungan', $wilayah->getNamaKabupaten());
        $this->assertSame('Tanjung Palas', $wilayah->getNamaKecamatan());
        $this->assertSame(2.83333300, $wilayah->getLatitudePusat());
        $this->assertSame(117.35000000, $wilayah->getLongitudePusat());
        $this->assertSame($polygon, $wilayah->getPolygonGeojson());

        $summary = $wilayah->toSummaryArray();
        $this->assertArrayNotHasKey('polygon_geojson', $summary);
        $this->assertSame('65.01.01', $summary['kode_wilayah']);

        $full = $wilayah->jsonSerialize();
        $this->assertArrayHasKey('polygon_geojson', $full);
        $this->assertSame($polygon, $full['polygon_geojson']);
    }
}
