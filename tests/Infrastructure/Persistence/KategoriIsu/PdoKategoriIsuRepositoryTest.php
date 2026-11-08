<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\KategoriIsu;

use App\Domain\KategoriIsu\KategoriIsuNotFoundException;
use App\Infrastructure\Persistence\KategoriIsu\PdoKategoriIsuRepository;
use PDO;
use PDOStatement;
use Tests\TestCase;

class PdoKategoriIsuRepositoryTest extends TestCase
{
    public function testFindAllReturnsMappedEntities(): void
    {
        $pdo = $this->prophesize(PDO::class);
        $stmt = $this->prophesize(PDOStatement::class);

        $stmt->fetch()
            ->willReturn(
                [
                    'id' => 1,
                    'nama_kategori' => 'Pendirian Rumah Ibadah',
                    'deskripsi' => 'Deskripsi 1',
                ],
                [
                    'id' => 2,
                    'nama_kategori' => 'Gesekan SARA',
                    'deskripsi' => 'Deskripsi 2',
                ],
                false
            );

        $pdo->query('SELECT id, nama_kategori, deskripsi FROM kategori_isu ORDER BY id ASC')
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $repository = new PdoKategoriIsuRepository($pdoInstance);

        $items = $repository->findAll();
        $this->assertCount(2, $items);
        $this->assertSame(1, $items[0]->getId());
        $this->assertSame('Pendirian Rumah Ibadah', $items[0]->getNamaKategori());
        $this->assertSame(2, $items[1]->getId());
    }

    public function testFindByIdReturnsMatchingEntity(): void
    {
        $pdo = $this->prophesize(PDO::class);
        $stmt = $this->prophesize(PDOStatement::class);

        $stmt->execute(['id' => 1])->shouldBeCalledOnce();
        $stmt->fetch()->willReturn([
            'id' => 1,
            'nama_kategori' => 'Pendirian Rumah Ibadah',
            'deskripsi' => 'Deskripsi 1',
        ])->shouldBeCalledOnce();

        $pdo->prepare('SELECT id, nama_kategori, deskripsi FROM kategori_isu WHERE id = :id LIMIT 1')
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $repository = new PdoKategoriIsuRepository($pdoInstance);

        $entity = $repository->findById(1);
        $this->assertSame(1, $entity->getId());
        $this->assertSame('Pendirian Rumah Ibadah', $entity->getNamaKategori());
    }

    public function testFindByIdThrowsNotFoundException(): void
    {
        $this->expectException(KategoriIsuNotFoundException::class);

        $pdo = $this->prophesize(PDO::class);
        $stmt = $this->prophesize(PDOStatement::class);

        $stmt->execute(['id' => 999])->shouldBeCalledOnce();
        $stmt->fetch()->willReturn(false)->shouldBeCalledOnce();

        $pdo->prepare('SELECT id, nama_kategori, deskripsi FROM kategori_isu WHERE id = :id LIMIT 1')
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $repository = new PdoKategoriIsuRepository($pdoInstance);

        $repository->findById(999);
    }
}
