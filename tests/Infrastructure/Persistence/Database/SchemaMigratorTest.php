<?php

declare(strict_types=1);

namespace Tests\Infrastructure\Persistence\Database;

use App\Infrastructure\Persistence\Database\SchemaDefinition;
use App\Infrastructure\Persistence\Database\SchemaMigrator;
use PDO;
use Prophecy\Argument;
use Tests\TestCase;

class SchemaMigratorTest extends TestCase
{
    public function testTableOrderContainsExactlyTwelveTables(): void
    {
        $this->assertCount(12, SchemaDefinition::TABLE_ORDER);
        $this->assertSame([
            'wilayah',
            'users',
            'kategori_isu',
            'laporan_ews',
            'bukti_laporan',
            'tindak_lanjut',
            'kategori_media',
            'media_hub',
            'faq_chatbot',
            'rumah_ibadah',
            'agenda_kerukunan',
            'kontak_fkub',
        ], SchemaDefinition::TABLE_ORDER);
    }

    public function testStatementsContainExpectedStructure(): void
    {
        $statements = SchemaDefinition::getCreateTableStatements();
        $this->assertCount(12, $statements);

        foreach (SchemaDefinition::TABLE_ORDER as $table) {
            $this->assertArrayHasKey($table, $statements);
            $this->assertStringContainsString("CREATE TABLE IF NOT EXISTS {$table}", $statements[$table]);
        }

        // Test critical ENUMs and foreign key definitions
        $this->assertStringContainsString("'superadmin', 'pimpinan', 'verifikator'", $statements['users']);
        $this->assertStringContainsString(
            "status_akun ENUM('menunggu_verifikasi', 'aktif', 'ditolak', 'nonaktif')",
            $statements['users']
        );
        $this->assertStringContainsString(
            "status_kerawanan ENUM('hijau', 'kuning', 'merah')",
            $statements['laporan_ews']
        );
        $this->assertStringContainsString(
            "'draft', 'terkirim', 'diverifikasi', 'proses_mediasi', 'selesai', 'ditolak_hoaks'",
            $statements['laporan_ews']
        );
        $this->assertStringContainsString(
            "CONSTRAINT fk_laporan_wilayah FOREIGN KEY (wilayah_id) REFERENCES wilayah(id)",
            $statements['laporan_ews']
        );
    }

    public function testMigrateExecutesStatementsInOrder(): void
    {
        $pdoProphecy = $this->prophesize(PDO::class);
        $statements = SchemaDefinition::getCreateTableStatements();

        foreach (SchemaDefinition::TABLE_ORDER as $table) {
            $pdoProphecy->exec($statements[$table])->willReturn(0)->shouldBeCalledOnce();
        }

        /** @var PDO $pdo */
        $pdo = $pdoProphecy->reveal();

        $migrator = new SchemaMigrator($pdo);
        $migrated = $migrator->migrate();

        $this->assertSame(SchemaDefinition::TABLE_ORDER, $migrated);
    }

    public function testDropAllExecutesInReverseOrderWithForeignKeyChecks(): void
    {
        $pdoProphecy = $this->prophesize(PDO::class);

        $pdoProphecy->exec('SET FOREIGN_KEY_CHECKS = 0;')->willReturn(0)->shouldBeCalledOnce();

        $reversed = array_reverse(SchemaDefinition::TABLE_ORDER);
        foreach ($reversed as $table) {
            $pdoProphecy->exec("DROP TABLE IF EXISTS {$table};")->willReturn(0)->shouldBeCalledOnce();
        }

        $pdoProphecy->exec('SET FOREIGN_KEY_CHECKS = 1;')->willReturn(0)->shouldBeCalledOnce();

        /** @var PDO $pdo */
        $pdo = $pdoProphecy->reveal();

        $migrator = new SchemaMigrator($pdo);
        $dropped = $migrator->dropAll();

        $this->assertSame($reversed, $dropped);
    }
}
