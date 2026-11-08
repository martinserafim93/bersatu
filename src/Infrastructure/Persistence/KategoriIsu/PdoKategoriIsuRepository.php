<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\KategoriIsu;

use App\Domain\KategoriIsu\KategoriIsu;
use App\Domain\KategoriIsu\KategoriIsuNotFoundException;
use App\Domain\KategoriIsu\KategoriIsuRepository;
use PDO;

class PdoKategoriIsuRepository implements KategoriIsuRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /**
     * @return array<int, KategoriIsu>
     */
    public function findAll(): array
    {
        $stmt = $this->pdo->query('SELECT id, nama_kategori, deskripsi FROM kategori_isu ORDER BY id ASC');
        if ($stmt === false) {
            return [];
        }

        $items = [];
        while ($row = $stmt->fetch()) {
            /** @var array{id: int|string, nama_kategori: string, deskripsi: string} $row */
            $items[] = new KategoriIsu(
                (int) $row['id'],
                $row['nama_kategori'],
                $row['deskripsi']
            );
        }

        return $items;
    }

    public function findById(int $id): KategoriIsu
    {
        $stmt = $this->pdo->prepare('SELECT id, nama_kategori, deskripsi FROM kategori_isu WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);

        /** @var array{id: int|string, nama_kategori: string, deskripsi: string}|false $row */
        $row = $stmt->fetch();
        if ($row === false) {
            throw new KategoriIsuNotFoundException();
        }

        return new KategoriIsu(
            (int) $row['id'],
            $row['nama_kategori'],
            $row['deskripsi']
        );
    }
}
