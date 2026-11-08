# Fix: Deprecation PHP 8.4 di scaffold User

**Type:** Fix
**Status:** verified
**Branch:** fix/deprecation-php-8-4-di-scaffold-user

## The problem

Baseline Verify gagal sebelum Fitur 1 dimulai. Diamati dengan `php vendor/squizlabs/php_codesniffer/bin/phpcs`, `php vendor/phpstan/phpstan/phpstan analyse --no-progress`, dan `php vendor/phpunit/phpunit/phpunit` pada PHP 8.5.12:

| Alat | Hasil sekarang |
|---|---|
| PHPCS | bersih (25/25) |
| PHPStan level 4 | **1 error**: `InMemoryUserRepository.php:21` "Deprecated in PHP 8.4: Parameter #1 $users (array) is implicitly nullable via default value null" |
| PHPUnit | 19 tes, 37 assertion, OK tetapi **3 risky** (ada output `Deprecated:` saat tes) |

Dua deprecation berasal dari kode kita (scaffold Slim skeleton):

1. `src/Infrastructure/Persistence/User/InMemoryUserRepository.php:21`: `public function __construct(array $users = null)`. Tipe nullable implisit, deprecated sejak PHP 8.4. `coding-standards.md` mewajibkan `?Type $param = null`.
2. `src/Application/Actions/User/ViewUserAction.php:19`: `"User of id `${userId}` was viewed."`. Sintaks `${var}` dalam string deprecated sejak PHP 8.2.

Sumber ketiga ada di `vendor/php-di/php-di` 6.4.0 (banyak "Implicitly marking parameter ... as nullable"). Itu kode pihak ketiga, bukan bagian fix ini (lihat Out of scope).

## The fix

Dua edit satu baris, tanpa perubahan perilaku:

- `InMemoryUserRepository.php:21`: `array $users = null` menjadi `?array $users = null`. Docblock `@param User[]|null $users` sudah benar, tidak diubah. Isi konstruktor (`$users ?? [...]`) tidak diubah.
- `ViewUserAction.php:19`: `${userId}` menjadi `{$userId}`. Teks log harus tetap persis sama: ``User of id `5` was viewed.``

Tidak boleh berubah: semua kode lain di `src/`, `app/`, `tests/`, `vendor/`, `composer.json`, `composer.lock`. Jangan menambah `@` / `error_reporting` / `#[\SuppressWarnings]` untuk menyembunyikan deprecation.

## Build steps

- [x] 1. Terapkan dua edit satu baris di atas.
  - **Done when:**
    - `php vendor/phpstan/phpstan/phpstan analyse --no-progress` berakhir `[OK] No errors`.
    - Output `php vendor/phpunit/phpunit/phpunit` tidak lagi memuat `ViewUserAction.php` maupun `InMemoryUserRepository.php`, dan hasil akhirnya `OK` dengan 19 tes.
    - `php vendor/squizlabs/php_codesniffer/bin/phpcs` tetap bersih.

## Verify

Jalankan tiga perintah ini dari root proyek, berurutan:

```
php vendor/squizlabs/php_codesniffer/bin/phpcs
php vendor/phpstan/phpstan/phpstan analyse --no-progress
php vendor/phpunit/phpunit/phpunit
```

Pakai `php vendor/...` langsung karena shim `vendor/bin/*` dan `composer test` gagal di Git Bash pada mesin ini (diamati: "?php: The filename ... is incorrect"). Di mesin lain `composer test` / `vendor/bin/phpcs` / `vendor/bin/phpstan` setara.

Hasil yang diharapkan: PHPCS bersih; PHPStan 0 error; PHPUnit `OK` dengan 19 tes. Tes yang tersisa berstatus *risky* hanya yang memuat `vendor/php-di` (deprecation pihak ketiga, tetap ada). Jumlah pastinya dicatat saat implement, tidak diasumsikan di sini.

Tes baru tidak diperlukan: `InMemoryUserRepositoryTest` dan `ViewUserActionTest` sudah menjalankan kedua jalur kode yang diedit.

## Out of scope

- Deprecation di `vendor/php-di/php-di` 6.4.0. Tes yang memuatnya tetap *risky*. Memperbaikinya berarti upgrade dependency (`composer update php-di/php-di`) atau mengubah konfigurasi PHPUnit. Itu keputusan terpisah dan belum diverifikasi kompatibel dengan Slim/`composer.json` di sini.
- `.github/workflows/tests.yml` masih memakai PHP 7.4/8.0/8.1.
- `phpunit.xml` memakai skema deprecated (peringatan "Migrate your XML configuration").
- Penambahan ekstensi ke `composer.json` (dikerjakan di Fitur 1).


<!-- blueprint:completion {"schemaVersion":2,"specBytes":3707,"specSha256":"dcf998f610c37b124cbce5b233b5ab39dbfbbf1139bff42f8598d7e7d84cd129","branch":"refs/heads/fix/deprecation-php-8-4-di-scaffold-user","head":"f459f0ed9207d2bc23e65caf6b06ef29281cffd9","baseRef":"refs/heads/main","baseCommit":"f459f0ed9207d2bc23e65caf6b06ef29281cffd9","sourceTree":"e0d2c2d317e43217377c3f0ca3b6f543806ae3aa","landing":"local-merge","absentOptional":[]} -->
