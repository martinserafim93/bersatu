<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Database;

use PDO;
use Throwable;

class DatabaseSeeder
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array{wilayah: int, kategori_isu: int, kategori_media: int}
     * @throws Throwable
     */
    public function seedAll(): array
    {
        $this->pdo->beginTransaction();

        try {
            $wilayahCount = $this->seedWilayah();
            $kategoriIsuCount = $this->seedKategoriIsu();
            $kategoriMediaCount = $this->seedKategoriMedia();

            $this->pdo->commit();

            return [
                'wilayah' => $wilayahCount,
                'kategori_isu' => $kategoriIsuCount,
                'kategori_media' => $kategoriMediaCount,
            ];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $e;
        }
    }

    private function seedWilayah(): int
    {
        $sql = 'INSERT INTO wilayah (
                    kode_wilayah, nama_kabupaten, nama_kecamatan, latitude_pusat, longitude_pusat, polygon_geojson
                ) VALUES (
                    :kode_wilayah, :nama_kabupaten, :nama_kecamatan, :latitude_pusat, :longitude_pusat, :polygon_geojson
                ) ON DUPLICATE KEY UPDATE
                    nama_kabupaten = VALUES(nama_kabupaten),
                    nama_kecamatan = VALUES(nama_kecamatan),
                    latitude_pusat = VALUES(latitude_pusat),
                    longitude_pusat = VALUES(longitude_pusat),
                    polygon_geojson = VALUES(polygon_geojson)';

        $stmt = $this->pdo->prepare($sql);
        $rows = WilayahSeedData::getRows();

        foreach ($rows as $row) {
            $stmt->execute([
                'kode_wilayah' => $row['kode_wilayah'],
                'nama_kabupaten' => $row['nama_kabupaten'],
                'nama_kecamatan' => $row['nama_kecamatan'],
                'latitude_pusat' => $row['latitude_pusat'],
                'longitude_pusat' => $row['longitude_pusat'],
                'polygon_geojson' => $row['polygon_geojson'],
            ]);
        }

        return count($rows);
    }

    private function seedKategoriIsu(): int
    {
        $sql = 'INSERT INTO kategori_isu (
                    id, nama_kategori, deskripsi
                ) VALUES (
                    :id, :nama_kategori, :deskripsi
                ) ON DUPLICATE KEY UPDATE
                    nama_kategori = VALUES(nama_kategori),
                    deskripsi = VALUES(deskripsi)';

        $stmt = $this->pdo->prepare($sql);
        $rows = KategoriIsuSeedData::getRows();

        foreach ($rows as $row) {
            $stmt->execute([
                'id' => $row['id'],
                'nama_kategori' => $row['nama_kategori'],
                'deskripsi' => $row['deskripsi'],
            ]);
        }

        return count($rows);
    }

    private function seedKategoriMedia(): int
    {
        $sql = 'INSERT INTO kategori_media (
                    id, nama_kategori, slug
                ) VALUES (
                    :id, :nama_kategori, :slug
                ) ON DUPLICATE KEY UPDATE
                    nama_kategori = VALUES(nama_kategori),
                    slug = VALUES(slug)';

        $stmt = $this->pdo->prepare($sql);
        $rows = KategoriIsuSeedData::getKategoriMediaRows();

        foreach ($rows as $row) {
            $stmt->execute([
                'id' => $row['id'],
                'nama_kategori' => $row['nama_kategori'],
                'slug' => $row['slug'],
            ]);
        }

        return count($rows);
    }
}
