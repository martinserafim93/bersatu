<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Wilayah;

use App\Domain\Wilayah\Wilayah;
use App\Domain\Wilayah\WilayahNotFoundException;
use App\Domain\Wilayah\WilayahRepository;
use PDO;

class PdoWilayahRepository implements WilayahRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @param bool $includeGeojson
     * @return array<int, Wilayah>
     */
    public function findAll(bool $includeGeojson = false): array
    {
        $geojsonField = $includeGeojson ? 'polygon_geojson' : 'NULL AS polygon_geojson';
        $sql = "SELECT id, kode_wilayah, nama_kabupaten, nama_kecamatan, latitude_pusat, longitude_pusat,
                       {$geojsonField}
                FROM wilayah
                ORDER BY kode_wilayah ASC";

        $stmt = $this->pdo->query($sql);
        if ($stmt === false) {
            return [];
        }

        $items = [];
        while ($row = $stmt->fetch()) {
            /** @var array{
             *     id: int|string,
             *     kode_wilayah: string,
             *     nama_kabupaten: string,
             *     nama_kecamatan: string,
             *     latitude_pusat: float|string,
             *     longitude_pusat: float|string,
             *     polygon_geojson: ?string
             * } $row */
            $items[] = new Wilayah(
                (int) $row['id'],
                $row['kode_wilayah'],
                $row['nama_kabupaten'],
                $row['nama_kecamatan'],
                (float) $row['latitude_pusat'],
                (float) $row['longitude_pusat'],
                $row['polygon_geojson']
            );
        }

        return $items;
    }

    /**
     * @param string $namaKabupaten
     * @param bool $includeGeojson
     * @return array<int, Wilayah>
     */
    public function findByKabupaten(string $namaKabupaten, bool $includeGeojson = false): array
    {
        $geojsonField = $includeGeojson ? 'polygon_geojson' : 'NULL AS polygon_geojson';
        $sql = "SELECT id, kode_wilayah, nama_kabupaten, nama_kecamatan, latitude_pusat, longitude_pusat,
                       {$geojsonField}
                FROM wilayah
                WHERE LOWER(nama_kabupaten) = LOWER(:kabupaten)
                ORDER BY kode_wilayah ASC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['kabupaten' => $namaKabupaten]);

        $items = [];
        while ($row = $stmt->fetch()) {
            /** @var array{
             *     id: int|string,
             *     kode_wilayah: string,
             *     nama_kabupaten: string,
             *     nama_kecamatan: string,
             *     latitude_pusat: float|string,
             *     longitude_pusat: float|string,
             *     polygon_geojson: ?string
             * } $row */
            $items[] = new Wilayah(
                (int) $row['id'],
                $row['kode_wilayah'],
                $row['nama_kabupaten'],
                $row['nama_kecamatan'],
                (float) $row['latitude_pusat'],
                (float) $row['longitude_pusat'],
                $row['polygon_geojson']
            );
        }

        return $items;
    }

    public function findById(int $id): Wilayah
    {
        $sql = 'SELECT id, kode_wilayah, nama_kabupaten, nama_kecamatan, latitude_pusat, longitude_pusat,
                       polygon_geojson
                FROM wilayah
                WHERE id = :id
                LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        /** @var array{
         *     id: int|string,
         *     kode_wilayah: string,
         *     nama_kabupaten: string,
         *     nama_kecamatan: string,
         *     latitude_pusat: float|string,
         *     longitude_pusat: float|string,
         *     polygon_geojson: ?string
         * }|false $row */
        $row = $stmt->fetch();
        if ($row === false) {
            throw new WilayahNotFoundException();
        }

        return new Wilayah(
            (int) $row['id'],
            $row['kode_wilayah'],
            $row['nama_kabupaten'],
            $row['nama_kecamatan'],
            (float) $row['latitude_pusat'],
            (float) $row['longitude_pusat'],
            $row['polygon_geojson']
        );
    }

    public function findByKodeWilayah(string $kodeWilayah): Wilayah
    {
        $sql = 'SELECT id, kode_wilayah, nama_kabupaten, nama_kecamatan, latitude_pusat, longitude_pusat,
                       polygon_geojson
                FROM wilayah
                WHERE kode_wilayah = :kode
                LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['kode' => $kodeWilayah]);

        /** @var array{
         *     id: int|string,
         *     kode_wilayah: string,
         *     nama_kabupaten: string,
         *     nama_kecamatan: string,
         *     latitude_pusat: float|string,
         *     longitude_pusat: float|string,
         *     polygon_geojson: ?string
         * }|false $row */
        $row = $stmt->fetch();
        if ($row === false) {
            throw new WilayahNotFoundException();
        }

        return new Wilayah(
            (int) $row['id'],
            $row['kode_wilayah'],
            $row['nama_kabupaten'],
            $row['nama_kecamatan'],
            (float) $row['latitude_pusat'],
            (float) $row['longitude_pusat'],
            $row['polygon_geojson']
        );
    }

    /**
     * @return array<int, string>
     */
    public function listKabupaten(): array
    {
        $sql = 'SELECT DISTINCT nama_kabupaten FROM wilayah ORDER BY id ASC';
        $stmt = $this->pdo->query($sql);
        if ($stmt === false) {
            return [];
        }

        $list = [];
        while ($row = $stmt->fetch()) {
            /** @var array{nama_kabupaten: string} $row */
            $list[] = $row['nama_kabupaten'];
        }

        return $list;
    }
}
