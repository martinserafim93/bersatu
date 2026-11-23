# Portal BERSATU

**Portal BERSATU** (*BERsama Saling jAga Toleransi Umat*, nama singkat/ikon PWA: **BERSATU**) adalah portal web responsif berbasis Progressive Web App (PWA) untuk *Strategi Penguatan Early Warning System (EWS) Melalui Promosi Kerukunan Umat Beragama Berbasis Digital* pada Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara (Proyek Perubahan PKN Tk. II Angkatan XXX Tahun 2026).

Portal ini dirancang untuk melayani **5 Kabupaten/Kota dan 55 Kecamatan** se-Kalimantan Utara (dengan lokus *piloting* awal di Kabupaten Malinau) termasuk wilayah pedalaman 3TP/*blank spot* dan kawasan perbatasan.

## Arsitektur & Teknologi

- **Backend:** PHP 8.4 (minimum PHP 8.1), Slim Framework 4, PHP-DI 6, Monolog 2
- **Basis Data:** MySQL (`InnoDB`, `utf8mb4`) melalui `PDO` native (`ext-pdo_mysql`)
- **Keamanan Identitas:** Enkripsi `AES-256-GCM` dan *blind index* `HMAC-SHA256` (`ext-openssl`) untuk perlindungan identitas pelapor
- **Antarmuka & PWA:** Server-rendered PHP templates (`templates/`) tanpa bundler Node.js, sistem desain `/impeccable` (`DESIGN.md`), `public/manifest.json`, dan Service Worker (`public/sw.js`)
- **Target Hosting:** InfinityFree (PHP 8.4 & MySQL, seluruh berkas sumber dijaga di bawah 1 MB)

## Struktur Direktori

- `app/` - Konfigurasi pengaturan, dependency injection, middleware, repository binding, dan rute Slim 4
- `bin/` - Utilitas CLI (`migrate.php`, `seed.php`, `create-superadmin.php`)
- `public/` - Web root (`index.php`, `manifest.json`, `sw.js`, dan aset statis di `public/assets/`)
- `src/` - Kode aplikasi (`Application/`, `Domain/`, `Infrastructure/`)
- `templates/` - Template HTML PHP sisi server
- `tests/` - Pengujian unit dan aksi PHPUnit
- `blueprint/` - Rencana proyek, build plan, standar kode, dan arsip fitur

## Persyaratan

- PHP `>= 8.1` (target produksi & Docker: **PHP 8.4**) dengan ekstensi `json`, `mbstring`, `openssl`, `pdo`, `pdo_mysql`
- Composer
- MySQL / MariaDB (untuk menjalankan aplikasi penuh, migrasi, dan seeder)

## Instalasi & Menjalankan Lokal

1. Pasang dependensi PHP:

   ```bash
   composer install
   ```

2. Salin berkas environment dan lengkapi konfigurasi basis data serta kunci enkripsi 64 karakter heksadesimal:

   ```bash
   cp .env.example .env
   php -r "echo bin2hex(random_bytes(32)) . PHP_EOL;"
   ```

   Isikan hasil kunci ke `APP_ENCRYPTION_KEY` di `.env` bersama `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, dan `DB_PASS`.

3. Jalankan migrasi 12 tabel dan seeder master data Kalimantan Utara:

   ```bash
   php bin/migrate.php
   php bin/seed.php
   ```

4. Buat akun `superadmin` awal (kata sandi dapat diberikan lewat env `SUPERADMIN_PASSWORD` atau prompt interaktif):

   ```bash
   php bin/create-superadmin.php \
     --email=superadmin@kaltara.kemenag.go.id \
     --nama="Super Administrator" \
     --nip-nik=198001012005011001 \
     --wa=081234567890 \
     --wilayah-kode=65.01.05 \
     --instansi="Kanwil Kemenag Kaltara"
   ```

5. Jalankan server pengembangan di `http://localhost:8080`:

   ```bash
   composer start
   ```

   Atau melalui Docker (`php:8.4-cli-alpine`):

   ```bash
   docker-compose up -d
   ```

## Pengujian & Kualitas Kode

- Pengujian PHPUnit:

  ```bash
  composer test
  ```

- Pemeriksaan gaya kode PSR-12 (`src/` dan `tests/`):

  ```bash
  vendor/bin/phpcs
  ```

- Analisis statis PHPStan level 4 (`src/`):

  ```bash
  vendor/bin/phpstan
  ```

## Dokumentasi Rujukan

- [AGENTS.md](AGENTS.md) - Ringkasan proyek, perintah kerja, dan panduan agen AI
- [PRODUCT.md](PRODUCT.md) - Cakupan produk, peran pengguna, batasan, dan aksesibilitas
- [DESIGN.md](DESIGN.md) - Sistem desain visual dan token antarmuka
- [blueprint/build-plan.md](blueprint/build-plan.md) - Daftar fitur dan progres implementasi
- [blueprint/context/coding-standards.md](blueprint/context/coding-standards.md) - Standar kode dan arsitektur
