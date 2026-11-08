<?php

declare(strict_types=1);

namespace App\Domain\KategoriIsu;

interface KategoriIsuRepository
{
    /**
     * @return array<int, KategoriIsu>
     */
    public function findAll(): array;

    /**
     * @param int $id
     * @return KategoriIsu
     * @throws KategoriIsuNotFoundException
     */
    public function findById(int $id): KategoriIsu;
}
