<?php

declare(strict_types=1);

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
$migrator = new SchemaMigrator($pdo);

echo "Memulai migrasi skema basis data 12 tabel Portal BERSATU...\n";
$tables = $migrator->migrate();

foreach ($tables as $index => $table) {
    echo sprintf(" [%d/12] Tabel '%s' berhasil dimigrasi.\n", $index + 1, $table);
}

echo "Migrasi selesai! Total " . count($tables) . " tabel siap digunakan.\n";
