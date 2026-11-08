<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Wilayah;

use App\Domain\Wilayah\WilayahNotFoundException;
use App\Infrastructure\Persistence\Wilayah\PdoWilayahRepository;
use PDO;
use PDOStatement;
use Tests\TestCase;

class PdoWilayahRepositoryTest extends TestCase
{
    public function testFindAllReturnsMappedEntities(): void
    {
        $pdo = $this->prophesize(PDO::class);
        $stmt = $this->prophesize(PDOStatement::class);

        $stmt->fetch()->willReturn(
            [
                'id' => 1,
                'kode_wilayah' => '65.01.01',
                'nama_kabupaten' => 'Kabupaten Bulungan',
                'nama_kecamatan' => 'Tanjung Palas',
                'latitude_pusat' => 2.833333,
                'longitude_pusat' => 117.35,
                'polygon_geojson' => null,
            ],
            false
        );

        $pdo->query('SELECT id, kode_wilayah, nama_kabupaten, nama_kecamatan, latitude_pusat, longitude_pusat,
                       NULL AS polygon_geojson
                FROM wilayah
                ORDER BY kode_wilayah ASC')
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $repository = new PdoWilayahRepository($pdoInstance);

        $items = $repository->findAll(false);
        $this->assertCount(1, $items);
        $this->assertSame('65.01.01', $items[0]->getKodeWilayah());
        $this->assertNull($items[0]->getPolygonGeojson());
    }

    public function testFindByKabupatenReturnsMatchingEntities(): void
    {
        $pdo = $this->prophesize(PDO::class);
        $stmt = $this->prophesize(PDOStatement::class);

        $stmt->execute(['kabupaten' => 'Kabupaten Malinau'])->shouldBeCalledOnce();
        $stmt->fetch()->willReturn(
            [
                'id' => 11,
                'kode_wilayah' => '65.02.01',
                'nama_kabupaten' => 'Kabupaten Malinau',
                'nama_kecamatan' => 'Mentarang',
                'latitude_pusat' => 3.483333,
                'longitude_pusat' => 116.483333,
                'polygon_geojson' => '{"type":"Feature"}',
            ],
            false
        );

        $pdo->prepare('SELECT id, kode_wilayah, nama_kabupaten, nama_kecamatan, latitude_pusat, longitude_pusat,
                       polygon_geojson
                FROM wilayah
                WHERE LOWER(nama_kabupaten) = LOWER(:kabupaten)
                ORDER BY kode_wilayah ASC')
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $repository = new PdoWilayahRepository($pdoInstance);

        $items = $repository->findByKabupaten('Kabupaten Malinau', true);
        $this->assertCount(1, $items);
        $this->assertSame('Mentarang', $items[0]->getNamaKecamatan());
        $this->assertSame('{"type":"Feature"}', $items[0]->getPolygonGeojson());
    }

    public function testFindByIdThrowsNotFoundException(): void
    {
        $this->expectException(WilayahNotFoundException::class);

        $pdo = $this->prophesize(PDO::class);
        $stmt = $this->prophesize(PDOStatement::class);

        $stmt->execute(['id' => 999])->shouldBeCalledOnce();
        $stmt->fetch()->willReturn(false)->shouldBeCalledOnce();

        $pdo->prepare('SELECT id, kode_wilayah, nama_kabupaten, nama_kecamatan, latitude_pusat, longitude_pusat,
                       polygon_geojson
                FROM wilayah
                WHERE id = :id
                LIMIT 1')
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $repository = new PdoWilayahRepository($pdoInstance);

        $repository->findById(999);
    }

    public function testFindByKodeWilayahReturnsEntity(): void
    {
        $pdo = $this->prophesize(PDO::class);
        $stmt = $this->prophesize(PDOStatement::class);

        $stmt->execute(['kode' => '65.02.01'])->shouldBeCalledOnce();
        $stmt->fetch()->willReturn([
            'id' => 11,
            'kode_wilayah' => '65.02.01',
            'nama_kabupaten' => 'Kabupaten Malinau',
            'nama_kecamatan' => 'Mentarang',
            'latitude_pusat' => 3.483333,
            'longitude_pusat' => 116.483333,
            'polygon_geojson' => null,
        ])->shouldBeCalledOnce();

        $pdo->prepare('SELECT id, kode_wilayah, nama_kabupaten, nama_kecamatan, latitude_pusat, longitude_pusat,
                       polygon_geojson
                FROM wilayah
                WHERE kode_wilayah = :kode
                LIMIT 1')
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $repository = new PdoWilayahRepository($pdoInstance);

        $entity = $repository->findByKodeWilayah('65.02.01');
        $this->assertSame('Mentarang', $entity->getNamaKecamatan());
    }

    public function testListKabupatenReturnsList(): void
    {
        $pdo = $this->prophesize(PDO::class);
        $stmt = $this->prophesize(PDOStatement::class);

        $stmt->fetch()->willReturn(
            ['nama_kabupaten' => 'Kabupaten Bulungan'],
            ['nama_kabupaten' => 'Kabupaten Malinau'],
            false
        );

        $pdo->query('SELECT DISTINCT nama_kabupaten FROM wilayah ORDER BY id ASC')
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $repository = new PdoWilayahRepository($pdoInstance);

        $list = $repository->listKabupaten();
        $this->assertSame(['Kabupaten Bulungan', 'Kabupaten Malinau'], $list);
    }
}
