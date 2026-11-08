<?php

declare(strict_types=1);

namespace App\Domain\Wilayah;

use JsonSerializable;

class Wilayah implements JsonSerializable
{
    public function __construct(
        private int $id,
        private string $kodeWilayah,
        private string $namaKabupaten,
        private string $namaKecamatan,
        private float $latitudePusat,
        private float $longitudePusat,
        private ?string $polygonGeojson = null
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getKodeWilayah(): string
    {
        return $this->kodeWilayah;
    }

    public function getNamaKabupaten(): string
    {
        return $this->namaKabupaten;
    }

    public function getNamaKecamatan(): string
    {
        return $this->namaKecamatan;
    }

    public function getLatitudePusat(): float
    {
        return $this->latitudePusat;
    }

    public function getLongitudePusat(): float
    {
        return $this->longitudePusat;
    }

    public function getPolygonGeojson(): ?string
    {
        return $this->polygonGeojson;
    }

    /**
     * @return array<string, mixed>
     */
    public function toSummaryArray(): array
    {
        return [
            'id' => $this->id,
            'kode_wilayah' => $this->kodeWilayah,
            'nama_kabupaten' => $this->namaKabupaten,
            'nama_kecamatan' => $this->namaKecamatan,
            'latitude_pusat' => $this->latitudePusat,
            'longitude_pusat' => $this->longitudePusat,
        ];
    }

    #[\ReturnTypeWillChange]
    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'kode_wilayah' => $this->kodeWilayah,
            'nama_kabupaten' => $this->namaKabupaten,
            'nama_kecamatan' => $this->namaKecamatan,
            'latitude_pusat' => $this->latitudePusat,
            'longitude_pusat' => $this->longitudePusat,
            'polygon_geojson' => $this->polygonGeojson,
        ];
    }
}
