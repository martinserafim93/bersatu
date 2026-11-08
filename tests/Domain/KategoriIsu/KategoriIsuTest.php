<?php

declare(strict_types=1);

namespace Tests\Domain\KategoriIsu;

use App\Domain\KategoriIsu\KategoriIsu;
use App\Infrastructure\Persistence\Database\KategoriIsuSeedData;
use Tests\TestCase;

class KategoriIsuTest extends TestCase
{
    public function testGettersAndJsonSerialize(): void
    {
        $kategori = new KategoriIsu(1, 'Pendirian Rumah Ibadah', 'Deskripsi terkait izin');

        $this->assertSame(1, $kategori->getId());
        $this->assertSame('Pendirian Rumah Ibadah', $kategori->getNamaKategori());
        $this->assertSame('Deskripsi terkait izin', $kategori->getDeskripsi());

        $json = $kategori->jsonSerialize();
        $this->assertSame([
            'id' => 1,
            'nama_kategori' => 'Pendirian Rumah Ibadah',
            'deskripsi' => 'Deskripsi terkait izin',
        ], $json);
    }

    public function testSeedDataContainsFiveEwsCategories(): void
    {
        $rows = KategoriIsuSeedData::getRows();
        $this->assertCount(5, $rows);

        $expectedNames = [
            'Pendirian Rumah Ibadah',
            'Gesekan Antarumat/Ormas SARA',
            'Penyiaran Agama/Penistaan',
            'Aliran Keagamaan Baru',
            'Hoaks Transnasional Perbatasan',
        ];

        foreach ($rows as $index => $row) {
            $this->assertSame($index + 1, $row['id']);
            $this->assertSame($expectedNames[$index], $row['nama_kategori']);
            $this->assertNotEmpty($row['deskripsi']);
        }
    }

    public function testSeedDataContainsFourMediaCategories(): void
    {
        $rows = KategoriIsuSeedData::getKategoriMediaRows();
        $this->assertCount(4, $rows);

        $expectedSlugs = [
            'moderasi-beragama',
            'ekoteologi',
            'regulasi-pbm-2006',
            'harmoni-perbatasan',
        ];

        foreach ($rows as $index => $row) {
            $this->assertSame($index + 1, $row['id']);
            $this->assertSame($expectedSlugs[$index], $row['slug']);
        }
    }
}
