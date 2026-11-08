<?php

declare(strict_types=1);

namespace App\Domain\Wilayah;

interface WilayahRepository
{
    /**
     * @param bool $includeGeojson
     * @return array<int, Wilayah>
     */
    public function findAll(bool $includeGeojson = false): array;

    /**
     * @param string $namaKabupaten
     * @param bool $includeGeojson
     * @return array<int, Wilayah>
     */
    public function findByKabupaten(string $namaKabupaten, bool $includeGeojson = false): array;

    /**
     * @param int $id
     * @return Wilayah
     * @throws WilayahNotFoundException
     */
    public function findById(int $id): Wilayah;

    /**
     * @param string $kodeWilayah
     * @return Wilayah
     * @throws WilayahNotFoundException
     */
    public function findByKodeWilayah(string $kodeWilayah): Wilayah;

    /**
     * @return array<int, string>
     */
    public function listKabupaten(): array;
}
