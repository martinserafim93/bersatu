<?php

declare(strict_types=1);

use App\Domain\User\AccountService;
use App\Domain\User\DuplicateUserException;
use App\Domain\User\ValidationException;
use App\Domain\Wilayah\WilayahNotFoundException;
use App\Domain\Wilayah\WilayahRepository;
use App\Infrastructure\Config\EnvLoader;
use DI\ContainerBuilder;

require __DIR__ . '/../vendor/autoload.php';

EnvLoader::load(__DIR__ . '/../.env');

$options = getopt('', [
    'email:',
    'nama:',
    'nip-nik:',
    'wa:',
    'wilayah-kode:',
    'instansi::',
]);

$requiredKeys = ['email', 'nama', 'nip-nik', 'wa', 'wilayah-kode'];
$missing = [];
foreach ($requiredKeys as $key) {
    if (!isset($options[$key]) || !is_string($options[$key]) || trim($options[$key]) === '') {
        $missing[] = '--' . $key;
    }
}

if (!empty($missing)) {
    fwrite(STDERR, "Argumen wajib belum lengkap: " . implode(', ', $missing) . "\n");
    fwrite(STDERR, "Penggunaan:\n");
    fwrite(
        STDERR,
        "  php bin/create-superadmin.php"
        . " --email=superadmin@kaltara.kemenag.go.id"
        . " --nama=\"Super Administrator\""
        . " --nip-nik=198001012005011001"
        . " --wa=081234567890"
        . " --wilayah-kode=65.01.05"
        . " [--instansi=\"Kanwil Kemenag Kaltara\"]\n"
    );
    exit(1);
}

$containerBuilder = new ContainerBuilder();

$settings = require __DIR__ . '/../app/settings.php';
$settings($containerBuilder);

$dependencies = require __DIR__ . '/../app/dependencies.php';
$dependencies($containerBuilder);

$repositories = require __DIR__ . '/../app/repositories.php';
$repositories($containerBuilder);

$container = $containerBuilder->build();

/** @var WilayahRepository $wilayahRepo */
$wilayahRepo = $container->get(WilayahRepository::class);

$kodeWilayah = trim((string) $options['wilayah-kode']);
try {
    $wilayah = $wilayahRepo->findByKodeWilayah($kodeWilayah);
} catch (WilayahNotFoundException) {
    fwrite(STDERR, "Kode wilayah tidak ditemukan.\n");
    exit(1);
}

$password = getenv('SUPERADMIN_PASSWORD');
if ($password === false || $password === '') {
    $password = $_ENV['SUPERADMIN_PASSWORD'] ?? '';
}

if (!is_string($password) || $password === '') {
    fwrite(STDOUT, 'Kata sandi: ');
    $password = trim((string) fgets(STDIN));
}

if ($password === '') {
    fwrite(STDERR, "Kata sandi tidak boleh kosong.\n");
    exit(1);
}

$instansi = isset($options['instansi']) && is_string($options['instansi']) && trim($options['instansi']) !== ''
    ? trim($options['instansi'])
    : null;

/** @var AccountService $accounts */
$accounts = $container->get(AccountService::class);

try {
    $userId = $accounts->createSuperadmin([
        'email' => trim((string) $options['email']),
        'nama_lengkap' => trim((string) $options['nama']),
        'nip_nik' => trim((string) $options['nip-nik']),
        'no_wa' => trim((string) $options['wa']),
        'wilayah_id' => $wilayah->getId(),
        'password' => $password,
        'instansi' => $instansi,
    ]);
} catch (ValidationException $e) {
    fwrite(STDERR, "Galat validasi:\n");
    foreach ($e->getErrors() as $field => $msg) {
        fwrite(STDERR, " - {$field}: {$msg}\n");
    }
    exit(1);
} catch (DuplicateUserException $e) {
    fwrite(STDERR, $e->getMessage() . "\n");
    exit(1);
}

fwrite(STDOUT, "Superadmin berhasil dibuat (id {$userId}).\n");
exit(0);
