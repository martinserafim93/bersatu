<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Database;

use App\Infrastructure\Persistence\Database\DatabaseSeeder;
use PDO;
use PDOStatement;
use Prophecy\Argument;
use RuntimeException;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    public function testSeedAllExecutesAllSeedsInsideTransaction(): void
    {
        $pdo = $this->prophesize(PDO::class);
        $wilayahStmt = $this->prophesize(PDOStatement::class);
        $kategoriIsuStmt = $this->prophesize(PDOStatement::class);
        $kategoriMediaStmt = $this->prophesize(PDOStatement::class);

        $pdo->beginTransaction()->willReturn(true)->shouldBeCalledOnce();

        $wilayahStmt->execute(Argument::type('array'))->willReturn(true)->shouldBeCalledTimes(55);
        $kategoriIsuStmt->execute(Argument::type('array'))->willReturn(true)->shouldBeCalledTimes(5);
        $kategoriMediaStmt->execute(Argument::type('array'))->willReturn(true)->shouldBeCalledTimes(4);

        $pdo->prepare(Argument::containingString('INSERT INTO wilayah'))
            ->willReturn($wilayahStmt->reveal())
            ->shouldBeCalledOnce();
        $pdo->prepare(Argument::containingString('INSERT INTO kategori_isu'))
            ->willReturn($kategoriIsuStmt->reveal())
            ->shouldBeCalledOnce();
        $pdo->prepare(Argument::containingString('INSERT INTO kategori_media'))
            ->willReturn($kategoriMediaStmt->reveal())
            ->shouldBeCalledOnce();

        $pdo->commit()->willReturn(true)->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $seeder = new DatabaseSeeder($pdoInstance);

        $result = $seeder->seedAll();

        $this->assertSame([
            'wilayah' => 55,
            'kategori_isu' => 5,
            'kategori_media' => 4,
        ], $result);
    }

    public function testSeedAllRollsBackOnException(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('DB execution failed');

        $pdo = $this->prophesize(PDO::class);
        $stmt = $this->prophesize(PDOStatement::class);

        $pdo->beginTransaction()->willReturn(true)->shouldBeCalledOnce();
        $stmt->execute(Argument::type('array'))->willThrow(new RuntimeException('DB execution failed'));

        $pdo->prepare(Argument::containingString('INSERT INTO wilayah'))
            ->willReturn($stmt->reveal())
            ->shouldBeCalledOnce();
        $pdo->inTransaction()->willReturn(true)->shouldBeCalledOnce();
        $pdo->rollBack()->willReturn(true)->shouldBeCalledOnce();

        /** @var PDO $pdoInstance */
        $pdoInstance = $pdo->reveal();
        $seeder = new DatabaseSeeder($pdoInstance);

        $seeder->seedAll();
    }
}
