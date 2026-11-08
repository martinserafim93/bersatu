<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Database;

use App\Infrastructure\Persistence\Database\WilayahSeedData;
use Tests\TestCase;

class WilayahSeedDataTest extends TestCase
{
    public function testSeedDataContainsFiftyFiveSubdistrictsAcrossFiveRegencies(): void
    {
        $rows = WilayahSeedData::getRows();
        $this->assertCount(55, $rows);

        $countsByKabupaten = [];
        $codes = [];

        foreach ($rows as $row) {
            $kab = $row['nama_kabupaten'];
            $countsByKabupaten[$kab] = ($countsByKabupaten[$kab] ?? 0) + 1;

            $this->assertNotContains($row['kode_wilayah'], $codes, 'Kode wilayah harus unik');
            $codes[] = $row['kode_wilayah'];

            $this->assertNotEmpty($row['nama_kecamatan']);
            $this->assertGreaterThan(0.0, $row['latitude_pusat']);
            $this->assertGreaterThan(100.0, $row['longitude_pusat']);

            // Validate GeoJSON structure
            $geo = json_decode($row['polygon_geojson'], true);
            $this->assertIsArray($geo);
            $this->assertSame('Feature', $geo['type']);
            $this->assertSame('Polygon', $geo['geometry']['type']);

            $coords = $geo['geometry']['coordinates'][0];
            $this->assertCount(5, $coords, 'Polygon harus memiliki 5 titik koordinat tertutup');
            $this->assertSame($coords[0], $coords[4], 'Titik awal dan akhir cincin poligon harus identik');
        }

        $this->assertSame([
            'Kabupaten Bulungan' => 10,
            'Kabupaten Malinau' => 15,
            'Kabupaten Nunukan' => 21,
            'Kabupaten Tana Tidung' => 5,
            'Kota Tarakan' => 4,
        ], $countsByKabupaten);
    }
}
