<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Database\DatabaseSeeder;
use App\Infrastructure\Persistence\Database\SchemaMigrator;
use DI\ContainerBuilder;

require __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();

$settings = require __DIR__ . '/../app/settings.php';
$settings($containerBuilder);

$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($containerBuilder);

$repositories = require __DIR__ . '/../app/repositories.php';
$repositories($containerBuilder);

$container = $containerBuilder->build();

/** @var PDO $pdo */
$pdo = $container->get(PDO::class);

echo "1. Memastikan skema 12 tabel terpasang...\n";
$migrator = new SchemaMigrator($pdo);
$migrated = $migrator->migrate();
echo sprintf("   Skema terverifikasi (%d tabel).\n", count($migrated));

echo "2. Menjalankan seeder master data Kalimantan Utara...\n";
$seeder = new DatabaseSeeder($pdo);
$counts = $seeder->seedAll();

echo sprintf("   - Master Wilayah: %d kecamatan (5 Kab/Kota) berhasil di-seed.\n", $counts['wilayah']);
echo sprintf("   - Kategori Isu EWS: %d kategori berhasil di-seed.\n", $counts['kategori_isu']);
echo sprintf("   - Kategori Media Hub: %d kategori berhasil di-seed.\n", $counts['kategori_media']);
echo "Seeding selesai dengan sukses!\n";
