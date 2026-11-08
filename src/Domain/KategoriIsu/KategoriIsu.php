<?php

declare(strict_types=1);

namespace App\Domain\KategoriIsu;

use JsonSerializable;

class KategoriIsu implements JsonSerializable
{
    public function __construct(
        private int $id,
        private string $namaKategori,
        private string $deskripsi
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNamaKategori(): string
    {
        return $this->namaKategori;
    }

    public function getDeskripsi(): string
    {
        return $this->deskripsi;
    }

    #[\ReturnTypeWillChange]
    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'nama_kategori' => $this->namaKategori,
            'deskripsi' => $this->deskripsi,
        ];
    }
}
