# Feature: Skema Basis Data MySQL & Master Data Wilayah Kaltara (Tahap 1 - M1)

**From build-plan:** feature 1
**Build attempt:** 1
**Branch:** feature/skema-basis-data-mysql-master-data-wilayah-kaltara-tahap-1-m1
**Status:** verified

## Goal

Membangun fondasi persistensi basis data **MySQL (`PDO` native)** untuk **Portal BERSATU**, mencakup konfigurasi koneksi PDO, definisi dan runner migrasi DDL untuk **12 tabel sistem yang terkunci**, seeder idempoten untuk **5 Kabupaten/Kota dan 55 Kecamatan se-Kalimantan Utara** (beserta titik koordinat pusat dan poligon batas wilayah GeoJSON) dan **5 klasifikasi kategori isu EWS**, serta repositori dan endpoint JSON *read-only* untuk menyediakan master data wilayah dan kategori isu bagi modul-modul berikutnya.

## In scope

- Konfigurasi database pada `app/settings.php`, dokumentasi variabel environment pada `.env.example`, dan registrasi layanan `PDO::class` secara *lazy* pada `app/dependencies.php`.
- Definisi skema SQL DDL (`CREATE TABLE IF NOT EXISTS ... ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci`) untuk seluruh **12 tabel inti** sesuai urutan ketergantungan *foreign key*:
  1. `wilayah`
  2. `users`
  3. `kategori_isu`
  4. `laporan_ews`
  5. `bukti_laporan`
  6. `tindak_lanjut`
  7. `kategori_media`
  8. `media_hub`
  9. `faq_chatbot`
  10. `rumah_ibadah`
  11. `agenda_kerukunan`
  12. `kontak_fkub`
- Layanan migrasi skema `SchemaMigrator` dan skrip CLI `bin/migrate.php` untuk mengeksekusi pembuatan 12 tabel secara berurutan.
- Dataset master referensi `WilayahSeedData` yang memuat tepat **55 Kecamatan** pada **5 Kabupaten/Kota** di Provinsi Kalimantan Utara (`Kabupaten Bulungan` 10 kecamatan, `Kabupaten Malinau` 15 kecamatan, `Kabupaten Nunukan` 21 kecamatan, `Kabupaten Tana Tidung` 5 kecamatan, `Kota Tarakan` 4 kecamatan) lengkap dengan `kode_wilayah` Kemendagri (`65.01.01` s.d. `65.71.04`), `latitude_pusat`, `longitude_pusat`, dan `polygon_geojson` berformat GeoJSON `Polygon` valid.
- Dataset master referensi `KategoriIsuSeedData` yang memuat tepat **5 klasifikasi kategori isu EWS** (`Pendirian Rumah Ibadah`, `Gesekan Antarumat/Ormas SARA`, `Penyiaran Agama/Penistaan`, `Aliran Keagamaan Baru`, `Hoaks Transnasional Perbatasan`) beserta deskripsinya, ditambah 4 kategori awal `kategori_media` (`Moderasi Beragama`, `Ekoteologi`, `Regulasi PBM 2006`, `Harmoni Perbatasan`).
- Layanan seeder idempoten `DatabaseSeeder` (`INSERT ... ON DUPLICATE KEY UPDATE`) dan skrip CLI `bin/seed.php`.
- Entitas domain, exception, kontrak repository, dan implementasi PDO untuk `Wilayah` (`src/Domain/Wilayah/`, `src/Infrastructure/Persistence/Wilayah/PdoWilayahRepository.php`) dan `KategoriIsu` (`src/Domain/KategoriIsu/`, `src/Infrastructure/Persistence/KategoriIsu/PdoKategoriIsuRepository.php`).
- Endpoint HTTP `GET /api/master/wilayah` (mendukung filter `?kabupaten=` dan `?include_geojson=1`) serta `GET /api/master/kategori-isu`.
- Pengujian unit dan action menggunakan PHPUnit (`composer test`), pemeriksaan PSR-12 (`vendor/bin/phpcs`), dan analisis statis PHPStan level 4 (`vendor/bin/phpstan`).

## Out of scope

- Pendaftaran mandiri pengguna, verifikasi akun oleh `superadmin`, login, manajemen sesi, middleware RBAC, dan layanan enkripsi `AES-256-GCM` (masuk pada **Fitur 2**).
- Antarmuka halaman web HTML/PWA, `manifest.json`, Service Worker, dan beranda kondusivitas (masuk pada **Fitur 3**).
- Input formulir "Lapor Dini", unggah bukti, dan verifikasi laporan EWS (masuk pada **Fitur 4 dan Fitur 5**).
- Visualisasi peta interaktif Leaflet.js dan pengiriman *instant alert* Telegram/WhatsApp (masuk pada **Fitur 6**).
- Operasi CRUD master data oleh `superadmin` melalui antarmuka admin (masuk pada **Fitur 9**).

## Build loop

- **Mode review langkah (`workflow.stepReview`):** `feature` - kerjakan dan verifikasi setiap langkah secara berurutan, lalu sajikan satu paket review lengkap di akhir fitur.
- **Komit checkpoint (`workflow.checkpointCommits`):** `disabled` - jangan membuat komit per langkah; komit fitur dibuat saat `/complete`.
- **Gate pengujian:** Setelah setiap langkah selesai, jalankan `composer test`, `vendor/bin/phpcs`, dan `vendor/bin/phpstan`. Seluruh perintah wajib lulus (`exit code 0`) sebelum lanjut ke langkah berikutnya.
- **Standar penulisan:** Wajib menyertakan `declare(strict_types=1);` di setiap file PHP, menggunakan tipe nullable eksplisit (`?Type $param = null`) agar kompatibel dengan PHP 8.4, menggunakan sintaks array pendek `[]`, dan **dilarang** menggunakan karakter *em dash*, *en dash*, atau *ellipsis* pada kode, komentar, maupun dokumentasi.

## Build steps

- [x] **Step 1: Konfigurasi Environment Database & Layanan Koneksi `PDO` MySQL**
  - **Tujuan:** Menyiapkan konfigurasi koneksi database di `app/settings.php`, file contoh `.env.example`, dan definisi `PDO::class` di `app/dependencies.php`.
  - **Instruksi Implementasi Detail:**
    1. Buat file `.env.example` di root proyek yang mendokumentasikan variabel environment berikut:
       ```ini
       APP_ENV=development
       APP_DEBUG=true
       APP_ENCRYPTION_KEY=ganti-dengan-64-karakter-hex-atau-32-byte-secret-key

       DB_DRIVER=mysql
       DB_HOST=127.0.0.1
       DB_PORT=3306
       DB_NAME=bersatu
       DB_USER=root
       DB_PASS=
       DB_CHARSET=utf8mb4

       TELEGRAM_BOT_TOKEN=
       TELEGRAM_CHAT_ID=
       TELEGRAM_API_BASE_URL=https://api.telegram.org

       WHATSAPP_API_URL=https://graph.facebook.com/v21.0
       WHATSAPP_API_TOKEN=
       ```
    2. Perbarui `app/settings.php` agar menambahkan kunci `'db'` di dalam array `new Settings([...])` dengan pembacaan dari `$_ENV` / `getenv()`:
       - `'driver' => (string) ($_ENV['DB_DRIVER'] ?? getenv('DB_DRIVER') ?: 'mysql')`
       - `'host' => (string) ($_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1')`
       - `'port' => (int) ($_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306)`
       - `'database' => (string) ($_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'bersatu')`
       - `'username' => (string) ($_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'root')`
       - `'password' => (string) ($_ENV['DB_PASS'] ?? getenv('DB_PASS') ?: '')`
       - `'charset' => (string) ($_ENV['DB_CHARSET'] ?? getenv('DB_CHARSET') ?: 'utf8mb4')`
       - `'flags' => [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_EMULATE_PREPARES => false]`
    3. Buat class helper `src/Infrastructure/Persistence/Database/PdoConnectionFactory.php` dengan method `create(array $dbSettings): PDO`:
       - Jika `$dbSettings['driver'] === 'sqlite'`, gunakan DSN `'sqlite:' . ($dbSettings['database'] ?: ':memory:')`.
       - Jika driver adalah `'mysql'` (default), bangun DSN `sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', $dbSettings['host'], (int) $dbSettings['port'], $dbSettings['database'], $dbSettings['charset'])` dan panggil `new PDO($dsn, $dbSettings['username'], $dbSettings['password'], $dbSettings['flags'])`.
       - Tambahkan juga method publik `buildDsn(array $dbSettings): string` agar pembentukan DSN dapat diuji secara unit tanpa membuka koneksi jaringan.
    4. Perbarui `app/dependencies.php` untuk mendaftarkan `PDO::class` menggunakan closure *lazy* yang mengambil `$c->get(SettingsInterface::class)->get('db')` dan memanggil `(new PdoConnectionFactory())->create($dbSettings)`.
    5. Buat pengujian unit `tests/Infrastructure/Persistence/Database/PdoConnectionFactoryTest.php` yang memverifikasi:
       - `SettingsInterface` pada container memuat konfigurasi `'db'` dengan nilai default yang benar.
       - `PdoConnectionFactory::buildDsn()` menghasilkan string DSN MySQL (`mysql:host=127.0.0.1;port=3306;dbname=bersatu;charset=utf8mb4`) dan DSN SQLite (`sqlite::memory:`) secara akurat.
  - **Done when:** `.env.example`, `app/settings.php`, `app/dependencies.php`, `PdoConnectionFactory.php`, dan `PdoConnectionFactoryTest.php` selesai dibuat, serta `composer test`, `vendor/bin/phpcs`, dan `vendor/bin/phpstan` lulus tanpa error.

- [x] **Step 2: Definisi Skema DDL 12 Tabel Inti & Runner Migrasi Basis Data (`SchemaDefinition` & `SchemaMigrator`)**
  - **Tujuan:** Menyediakan satu sumber kebenaran (*single source of truth*) untuk skema SQL 12 tabel MySQL yang terkunci beserta class migrator dan skrip CLI.
  - **Instruksi Implementasi Detail:**
    1. Buat class `src/Infrastructure/Persistence/Database/SchemaDefinition.php` dengan konstanta `TABLE_ORDER` (berisi 12 nama tabel sesuai urutan pembuatan) dan method statis `getCreateTableStatements(): array` yang mengembalikan array asosiatif `['nama_tabel' => 'CREATE TABLE IF NOT EXISTS ...']` untuk 12 tabel sesuai kontrak pada bagian **Data / contracts** di bawah.
    2. Buat class `src/Infrastructure/Persistence/Database/SchemaMigrator.php`:
       - Constructor menerima `PDO $pdo`.
       - Method `migrate(): array`: mengeksekusi setiap statement dari `SchemaDefinition::getCreateTableStatements()` menggunakan `$this->pdo->exec($sql)` sesuai urutan `SchemaDefinition::TABLE_ORDER`, lalu mengembalikan array daftar nama tabel yang berhasil dimigrasi (`string[]`).
       - Method `dropAll(): array`: mengeksekusi `SET FOREIGN_KEY_CHECKS = 0;`, menjalankan `DROP TABLE IF EXISTS <tabel>;` dalam urutan terbalik (`array_reverse(SchemaDefinition::TABLE_ORDER)`), mengembalikan `SET FOREIGN_KEY_CHECKS = 1;`, dan mengembalikan daftar tabel yang dihapus.
    3. Buat skrip CLI `bin/migrate.php` (dengan `declare(strict_types=1);`) yang memuat `vendor/autoload.php`, mengambil `PDO::class` dari container PHP-DI aplikasi, menjalankan `$migrator->migrate()`, dan mencetak nama 12 tabel yang dimigrasi ke `STDOUT`.
    4. Buat pengujian unit `tests/Infrastructure/Persistence/Database/SchemaMigratorTest.php` yang memverifikasi:
       - `SchemaDefinition::TABLE_ORDER` memiliki tepat 12 tabel dengan urutan ketergantungan *foreign key* yang benar.
       - Setiap statement DDL memuat nama kolom, tipe data, nilai `ENUM`, dan klausa `FOREIGN KEY` yang diwajibkan pada kontrak data.
       - `SchemaMigrator::migrate()` mengeksekusi tepat 12 statement `CREATE TABLE IF NOT EXISTS` pada mock `PDO` (`Prophecy`) dan mengembalikan ke-12 nama tabel.
       - `SchemaMigrator::dropAll()` mengeksekusi statement dalam urutan terbalik dengan `FOREIGN_KEY_CHECKS` dinonaktifkan sementara.
  - **Done when:** `SchemaDefinition.php`, `SchemaMigrator.php`, `bin/migrate.php`, dan `SchemaMigratorTest.php` selesai dibuat, serta `composer test`, `vendor/bin/phpcs`, dan `vendor/bin/phpstan` lulus tanpa error.

- [x] **Step 3: Domain Model, Repository & Dataset Seeder Kategori Isu EWS (`kategori_isu`)**
  - **Tujuan:** Membangun layer Domain dan Persistence untuk 5 klasifikasi kategori isu EWS beserta data seeder bakunya.
  - **Instruksi Implementasi Detail:**
    1. Buat `src/Domain/KategoriIsu/KategoriIsu.php` (mengimplementasikan `JsonSerializable`):
       - Properti private: `int $id`, `string $namaKategori`, `string $deskripsi`.
       - Getter: `getId(): int`, `getNamaKategori(): string`, `getDeskripsi(): string`.
       - `jsonSerialize(): array` mengembalikan `['id' => $this->id, 'nama_kategori' => $this->namaKategori, 'deskripsi' => $this->deskripsi]` dengan atribut `#[\ReturnTypeWillChange]`.
    2. Buat `src/Domain/KategoriIsu/KategoriIsuNotFoundException.php` yang mewarisi `App\Domain\DomainException\DomainRecordNotFoundException` dengan pesan `'Kategori isu yang diminta tidak ditemukan.'`.
    3. Buat interface `src/Domain/KategoriIsu/KategoriIsuRepository.php` dengan kontrak:
       - `public function findAll(): array;` (mengembalikan `KategoriIsu[]` urut `id ASC`)
       - `public function findById(int $id): KategoriIsu;` (melempar `KategoriIsuNotFoundException` bila tidak ditemukan)
    4. Buat `src/Infrastructure/Persistence/Database/KategoriIsuSeedData.php` dengan method statis `getRows(): array` untuk 5 kategori isu EWS dan `getKategoriMediaRows(): array` untuk 4 kategori media awal sesuai daftar pada bagian **Data / contracts**.
    5. Buat `src/Infrastructure/Persistence/KategoriIsu/PdoKategoriIsuRepository.php` yang mengimplementasikan `KategoriIsuRepository`:
       - Constructor: `public function __construct(private PDO $pdo)`.
       - `findAll()` menjalankan `SELECT id, nama_kategori, deskripsi FROM kategori_isu ORDER BY id ASC` dan memetakan setiap baris ke objek `KategoriIsu`.
       - `findById(int $id)` menjalankan `SELECT id, nama_kategori, deskripsi FROM kategori_isu WHERE id = :id LIMIT 1` dengan prepared statement, dan melempar `KategoriIsuNotFoundException` jika hasil `fetch()` bernilai `false`.
    6. Daftarkan `KategoriIsuRepository::class => \DI\autowire(PdoKategoriIsuRepository::class)` di `app/repositories.php`.
    7. Buat pengujian unit `tests/Domain/KategoriIsu/KategoriIsuTest.php` dan `tests/Infrastructure/Persistence/KategoriIsu/PdoKategoriIsuRepositoryTest.php` (menggunakan `Prophecy` untuk mock `PDO` dan `PDOStatement` pada kasus berhasil, kosong, dan `KategoriIsuNotFoundException`).
  - **Done when:** Seluruh class domain, seed data, repository `KategoriIsu`, dan unit test lulus `composer test`, `vendor/bin/phpcs`, dan `vendor/bin/phpstan`.

- [x] **Step 4: Domain Model, Repository & Seeder Master Data 5 Kabupaten/Kota & 55 Kecamatan Kaltara (`wilayah`)**
  - **Tujuan:** Membangun layer Domain, Persistence, dan dataset lengkap 55 Kecamatan pada 5 Kabupaten/Kota se-Kalimantan Utara beserta koordinat dan poligon GeoJSON, serta runner seeder terpadu `DatabaseSeeder`.
  - **Instruksi Implementasi Detail:**
    1. Buat `src/Domain/Wilayah/Wilayah.php` (mengimplementasikan `JsonSerializable`):
       - Properti private: `int $id`, `string $kodeWilayah`, `string $namaKabupaten`, `string $namaKecamatan`, `float $latitudePusat`, `float $longitudePusat`, `?string $polygonGeojson = null`.
       - Getter: `getId(): int`, `getKodeWilayah(): string`, `getNamaKabupaten(): string`, `getNamaKecamatan(): string`, `getLatitudePusat(): float`, `getLongitudePusat(): float`, `getPolygonGeojson(): ?string`.
       - Method `toSummaryArray(): array` mengembalikan data tanpa `polygon_geojson` (`id`, `kode_wilayah`, `nama_kabupaten`, `nama_kecamatan`, `latitude_pusat`, `longitude_pusat`) agar respons daftar ringan.
       - Method `jsonSerialize(): array` mengembalikan seluruh field termasuk `'polygon_geojson' => $this->polygonGeojson`.
    2. Buat `src/Domain/Wilayah/WilayahNotFoundException.php` yang mewarisi `DomainRecordNotFoundException` dengan pesan `'Data wilayah yang diminta tidak ditemukan.'`.
    3. Buat interface `src/Domain/Wilayah/WilayahRepository.php` dengan kontrak:
       - `public function findAll(bool $includeGeojson = false): array;`
       - `public function findByKabupaten(string $namaKabupaten, bool $includeGeojson = false): array;`
       - `public function findById(int $id): Wilayah;`
       - `public function findByKodeWilayah(string $kodeWilayah): Wilayah;`
       - `public function listKabupaten(): array;`
    4. Buat `src/Infrastructure/Persistence/Database/WilayahSeedData.php`:
       - Menyediakan method statis `getRows(): array` yang mengembalikan tepat **55 baris kecamatan** sesuai tabel referensi lengkap pada bagian **Data / contracts** di bawah.
       - Menyediakan helper statis `buildBoundingPolygonGeojson(string $kodeWilayah, string $namaKabupaten, string $namaKecamatan, float $lat, float $lng, float $delta = 0.045): string` yang menghasilkan string JSON GeoJSON `Feature` dengan geometri `Polygon` tertutup (`[[lng-d, lat-d], [lng+d, lat-d], [lng+d, lat+d], [lng-d, lat+d], [lng-d, lat-d]]`) sehingga setiap kecamatan memiliki poligon awal yang valid dan deterministik untuk dirender oleh Leaflet.js dan dapat diperbarui melalui Panel Admin pada Fitur 9.
    5. Buat `src/Infrastructure/Persistence/Wilayah/PdoWilayahRepository.php` yang mengimplementasikan `WilayahRepository` menggunakan prepared statements `PDO` dan memetakan baris database ke entitas `Wilayah`.
    6. Buat `src/Infrastructure/Persistence/Database/DatabaseSeeder.php`:
       - Constructor menerima `PDO $pdo`.
       - Method `seedAll(): array` menjalankan transaksi (`beginTransaction()` / `commit()`, dengan `rollBack()` jika gagal) untuk melakukan *upsert* (`INSERT INTO ... ON DUPLICATE KEY UPDATE ...`):
         - 55 baris `wilayah` dari `WilayahSeedData::getRows()` (berdasarkan kunci unik `kode_wilayah`).
         - 5 baris `kategori_isu` dari `KategoriIsuSeedData::getRows()` (berdasarkan `id`).
         - 4 baris `kategori_media` dari `KategoriIsuSeedData::getKategoriMediaRows()` (berdasarkan kunci unik `slug`).
       - Mengembalikan ringkasan jumlah baris yang di-*seed*: `['wilayah' => 55, 'kategori_isu' => 5, 'kategori_media' => 4]`.
    7. Buat skrip CLI `bin/seed.php` yang menjalankan `SchemaMigrator::migrate()` diikuti `DatabaseSeeder::seedAll()`.
    8. Daftarkan `WilayahRepository::class => \DI\autowire(PdoWilayahRepository::class)` di `app/repositories.php`.
    9. Buat pengujian unit `tests/Domain/Wilayah/WilayahTest.php`, `tests/Infrastructure/Persistence/Database/WilayahSeedDataTest.php`, `tests/Infrastructure/Persistence/Wilayah/PdoWilayahRepositoryTest.php`, dan `tests/Infrastructure/Persistence/Database/DatabaseSeederTest.php`.
  - **Done when:** Seluruh 55 kecamatan (10 Bulungan, 15 Malinau, 21 Nunukan, 5 Tana Tidung, 4 Tarakan) tervalidasi di unit test, `DatabaseSeeder` dan `PdoWilayahRepository` teruji penuh, serta `composer test`, `vendor/bin/phpcs`, dan `vendor/bin/phpstan` lulus tanpa error.

- [x] **Step 5: Endpoint HTTP Read-Only Master Wilayah & Kategori Isu (`/api/master/wilayah` & `/api/master/kategori-isu`)**
  - **Tujuan:** Mengekspos data master wilayah dan kategori isu melalui endpoint JSON Slim 4 untuk digunakan oleh formulir "Lapor Dini", filter Peta GIS, dan modul lainnya.
  - **Instruksi Implementasi Detail:**
    1. Buat `src/Application/Actions/Master/ListWilayahAction.php` yang mewarisi `App\Application\Actions\Action`:
       - Constructor menerima `LoggerInterface $logger` dan `WilayahRepository $wilayahRepository`.
       - Pada method `action(): Response`:
         - Baca query parameter opsional `kabupaten` (`trim((string) ($queryParams['kabupaten'] ?? ''))`) dan `include_geojson` (`in_array($queryParams['include_geojson'] ?? '', ['1', 'true'], true)`).
         - Jika `kabupaten` diisi, panggil `$this->wilayahRepository->findByKabupaten($kabupaten, $includeGeojson)`; jika kosong, panggil `$this->wilayahRepository->findAll($includeGeojson)`.
         - Jika `$includeGeojson === false`, konversi setiap item `Wilayah` menggunakan `$wilayah->toSummaryArray()`; jika `true`, gunakan serialisasi penuh `$wilayah->jsonSerialize()`.
         - Kembalikan `$this->respondWithData(['total' => count($items), 'kabupaten_list' => $this->wilayahRepository->listKabupaten(), 'items' => $items])`.
    2. Buat `src/Application/Actions/Master/ListKategoriIsuAction.php` yang mewarisi `App\Application\Actions\Action`:
       - Constructor menerima `LoggerInterface $logger` dan `KategoriIsuRepository $kategoriIsuRepository`.
       - Pada method `action(): Response`, panggil `$this->kategoriIsuRepository->findAll()` dan kembalikan `$this->respondWithData($items)`.
    3. Perbarui `app/routes.php` untuk menambahkan grup rute `/api/master`:
       - `GET /api/master/wilayah` -> `ListWilayahAction::class`
       - `GET /api/master/kategori-isu` -> `ListKategoriIsuAction::class`
    4. Buat pengujian action `tests/Application/Actions/Master/ListWilayahActionTest.php` dan `tests/Application/Actions/Master/ListKategoriIsuActionTest.php` yang menguji respons HTTP 200, struktur JSON `ActionPayload`, pemfilteran `?kabupaten=Kabupaten Malinau`, opsi `?include_geojson=1`, dan hasil kosong `[]` saat nama kabupaten tidak cocok.
  - **Done when:** Kedua endpoint `/api/master/wilayah` dan `/api/master/kategori-isu` terdaftar dan lulus seluruh pengujian di `composer test`, `vendor/bin/phpcs`, dan `vendor/bin/phpstan`.

## Files / areas

- **File Konfigurasi & Bootstrap yang Diubah:**
  - `.env.example` (baru)
  - `app/settings.php`
  - `app/dependencies.php`
  - `app/repositories.php`
  - `app/routes.php`
- **File Domain Baru:**
  - `src/Domain/Wilayah/Wilayah.php`
  - `src/Domain/Wilayah/WilayahRepository.php`
  - `src/Domain/Wilayah/WilayahNotFoundException.php`
  - `src/Domain/KategoriIsu/KategoriIsu.php`
  - `src/Domain/KategoriIsu/KategoriIsuRepository.php`
  - `src/Domain/KategoriIsu/KategoriIsuNotFoundException.php`
- **File Infrastructure & Persistence Baru:**
  - `src/Infrastructure/Persistence/Database/PdoConnectionFactory.php`
  - `src/Infrastructure/Persistence/Database/SchemaDefinition.php`
  - `src/Infrastructure/Persistence/Database/SchemaMigrator.php`
  - `src/Infrastructure/Persistence/Database/WilayahSeedData.php`
  - `src/Infrastructure/Persistence/Database/KategoriIsuSeedData.php`
  - `src/Infrastructure/Persistence/Database/DatabaseSeeder.php`
  - `src/Infrastructure/Persistence/Wilayah/PdoWilayahRepository.php`
  - `src/Infrastructure/Persistence/KategoriIsu/PdoKategoriIsuRepository.php`
- **File Application Action & Skrip CLI Baru:**
  - `src/Application/Actions/Master/ListWilayahAction.php`
  - `src/Application/Actions/Master/ListKategoriIsuAction.php`
  - `bin/migrate.php`
  - `bin/seed.php`
- **File Pengujian Baru:**
  - `tests/Infrastructure/Persistence/Database/PdoConnectionFactoryTest.php`
  - `tests/Infrastructure/Persistence/Database/SchemaMigratorTest.php`
  - `tests/Infrastructure/Persistence/Database/WilayahSeedDataTest.php`
  - `tests/Infrastructure/Persistence/Database/DatabaseSeederTest.php`
  - `tests/Domain/KategoriIsu/KategoriIsuTest.php`
  - `tests/Infrastructure/Persistence/KategoriIsu/PdoKategoriIsuRepositoryTest.php`
  - `tests/Domain/Wilayah/WilayahTest.php`
  - `tests/Infrastructure/Persistence/Wilayah/PdoWilayahRepositoryTest.php`
  - `tests/Application/Actions/Master/ListWilayahActionTest.php`
  - `tests/Application/Actions/Master/ListKategoriIsuActionTest.php`

## Data / contracts

### 1. Kontrak Skema SQL DDL 12 Tabel Terkunci (`SchemaDefinition`)

Urutan wajib pada `SchemaDefinition::TABLE_ORDER`:
`['wilayah', 'users', 'kategori_isu', 'laporan_ews', 'bukti_laporan', 'tindak_lanjut', 'kategori_media', 'media_hub', 'faq_chatbot', 'rumah_ibadah', 'agenda_kerukunan', 'kontak_fkub']`

Definisi kolom dan constraint yang wajib ada pada masing-masing tabel:

1. **`wilayah`**
   ```sql
   CREATE TABLE IF NOT EXISTS wilayah (
       id INT AUTO_INCREMENT PRIMARY KEY,
       kode_wilayah VARCHAR(10) NOT NULL UNIQUE,
       nama_kabupaten VARCHAR(100) NOT NULL,
       nama_kecamatan VARCHAR(100) NOT NULL,
       latitude_pusat DECIMAL(10,8) NOT NULL,
       longitude_pusat DECIMAL(11,8) NOT NULL,
       polygon_geojson LONGTEXT NULL,
       INDEX idx_wilayah_kabupaten (nama_kabupaten)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
   ```

2. **`users`**
   ```sql
   CREATE TABLE IF NOT EXISTS users (
       id BIGINT AUTO_INCREMENT PRIMARY KEY,
       nip_nik_encrypted TEXT NOT NULL,
       nip_nik_hash CHAR(64) NOT NULL UNIQUE,
       nama_lengkap VARCHAR(150) NOT NULL,
       instansi VARCHAR(150) NULL,
       no_wa_encrypted TEXT NOT NULL,
       email VARCHAR(100) NOT NULL UNIQUE,
       password_hash VARCHAR(255) NOT NULL,
       role ENUM('superadmin', 'pimpinan', 'verifikator', 'penyuluh_ipari', 'kua', 'fkub', 'masyarakat') NOT NULL DEFAULT 'masyarakat',
       wilayah_id INT NOT NULL,
       status_akun ENUM('menunggu_verifikasi', 'aktif', 'ditolak', 'nonaktif') NOT NULL DEFAULT 'menunggu_verifikasi',
       status_aktif BOOLEAN NOT NULL DEFAULT FALSE,
       created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
       updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
       CONSTRAINT fk_users_wilayah FOREIGN KEY (wilayah_id) REFERENCES wilayah(id) ON UPDATE CASCADE ON DELETE RESTRICT
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
   ```

3. **`kategori_isu`**
   ```sql
   CREATE TABLE IF NOT EXISTS kategori_isu (
       id INT AUTO_INCREMENT PRIMARY KEY,
       nama_kategori VARCHAR(100) NOT NULL,
       deskripsi TEXT NOT NULL
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
   ```

4. **`laporan_ews`**
   ```sql
   CREATE TABLE IF NOT EXISTS laporan_ews (
       id BIGINT AUTO_INCREMENT PRIMARY KEY,
       nomor_tiket VARCHAR(50) NOT NULL UNIQUE,
       pelapor_id BIGINT NOT NULL,
       wilayah_id INT NOT NULL,
       alamat_detail TEXT NOT NULL,
       latitude DECIMAL(10,8) NOT NULL,
       longitude DECIMAL(11,8) NOT NULL,
       kategori_id INT NOT NULL,
       judul_laporan VARCHAR(255) NOT NULL,
       tanggal_kejadian DATE NOT NULL,
       estimasi_pihak_terlibat VARCHAR(100) NULL,
       kronologi TEXT NOT NULL,
       status_kerawanan ENUM('hijau', 'kuning', 'merah') NOT NULL DEFAULT 'hijau',
       status_laporan ENUM('draft', 'terkirim', 'diverifikasi', 'proses_mediasi', 'selesai', 'ditolak_hoaks') NOT NULL DEFAULT 'terkirim',
       alert_terkirim_at DATETIME NULL,
       created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
       updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
       INDEX idx_laporan_wilayah_status (wilayah_id, status_laporan, status_kerawanan),
       CONSTRAINT fk_laporan_pelapor FOREIGN KEY (pelapor_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
       CONSTRAINT fk_laporan_wilayah FOREIGN KEY (wilayah_id) REFERENCES wilayah(id) ON UPDATE CASCADE ON DELETE RESTRICT,
       CONSTRAINT fk_laporan_kategori FOREIGN KEY (kategori_id) REFERENCES kategori_isu(id) ON UPDATE CASCADE ON DELETE RESTRICT
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
   ```

5. **`bukti_laporan`**
   ```sql
   CREATE TABLE IF NOT EXISTS bukti_laporan (
       id BIGINT AUTO_INCREMENT PRIMARY KEY,
       laporan_id BIGINT NOT NULL,
       file_path VARCHAR(255) NOT NULL,
       tipe_file VARCHAR(50) NOT NULL,
       caption VARCHAR(255) NULL,
       created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
       CONSTRAINT fk_bukti_laporan FOREIGN KEY (laporan_id) REFERENCES laporan_ews(id) ON UPDATE CASCADE ON DELETE CASCADE
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
   ```

6. **`tindak_lanjut`**
   ```sql
   CREATE TABLE IF NOT EXISTS tindak_lanjut (
       id BIGINT AUTO_INCREMENT PRIMARY KEY,
       laporan_id BIGINT NOT NULL,
       petugas_id BIGINT NOT NULL,
       catatan_analisis TEXT NOT NULL,
       rekomendasi_mitigasi TEXT NOT NULL,
       perubahan_status_kerawanan ENUM('hijau', 'kuning', 'merah') NOT NULL,
       tanggal_penanganan DATETIME NOT NULL,
       CONSTRAINT fk_tindak_lanjut_laporan FOREIGN KEY (laporan_id) REFERENCES laporan_ews(id) ON UPDATE CASCADE ON DELETE CASCADE,
       CONSTRAINT fk_tindak_lanjut_petugas FOREIGN KEY (petugas_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
   ```

7. **`kategori_media`**
   ```sql
   CREATE TABLE IF NOT EXISTS kategori_media (
       id INT AUTO_INCREMENT PRIMARY KEY,
       nama_kategori VARCHAR(100) NOT NULL,
       slug VARCHAR(100) NOT NULL UNIQUE
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
   ```

8. **`media_hub`**
   ```sql
   CREATE TABLE IF NOT EXISTS media_hub (
       id INT AUTO_INCREMENT PRIMARY KEY,
       kategori_id INT NULL,
       agama ENUM('semua_agama', 'islam', 'kristen', 'katolik', 'hindu', 'buddha', 'khonghucu') NOT NULL DEFAULT 'semua_agama',
       judul VARCHAR(255) NOT NULL,
       slug VARCHAR(255) NOT NULL UNIQUE,
       tipe_konten ENUM('video', 'animasi', 'infografis', 'podcast', 'naskah_khotbah') NOT NULL,
       url_file VARCHAR(255) NOT NULL,
       thumbnail_url VARCHAR(255) NULL,
       deskripsi TEXT NOT NULL,
       tokoh_narasumber VARCHAR(150) NOT NULL,
       status_tampil BOOLEAN NOT NULL DEFAULT TRUE,
       published_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
       CONSTRAINT fk_media_kategori FOREIGN KEY (kategori_id) REFERENCES kategori_media(id) ON UPDATE CASCADE ON DELETE SET NULL
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
   ```

9. **`faq_chatbot`**
   ```sql
   CREATE TABLE IF NOT EXISTS faq_chatbot (
       id INT AUTO_INCREMENT PRIMARY KEY,
       pertanyaan_kunci VARCHAR(255) NOT NULL,
       kata_kunci VARCHAR(255) NOT NULL,
       jawaban TEXT NOT NULL,
       kategori ENUM('pbm_2006', 'moderasi', 'ekoteologi', 'perbatasan') NOT NULL
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
   ```

10. **`rumah_ibadah`**
    ```sql
    CREATE TABLE IF NOT EXISTS rumah_ibadah (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_rumah_ibadah VARCHAR(150) NOT NULL,
        agama ENUM('islam', 'kristen', 'katolik', 'hindu', 'buddha', 'khonghucu') NOT NULL,
        wilayah_id INT NOT NULL,
        alamat TEXT NOT NULL,
        latitude DECIMAL(10,8) NULL,
        longitude DECIMAL(11,8) NULL,
        status_verifikasi BOOLEAN NOT NULL DEFAULT TRUE,
        CONSTRAINT fk_rumah_ibadah_wilayah FOREIGN KEY (wilayah_id) REFERENCES wilayah(id) ON UPDATE CASCADE ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ```

11. **`agenda_kerukunan`**
    ```sql
    CREATE TABLE IF NOT EXISTS agenda_kerukunan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        judul_agenda VARCHAR(200) NOT NULL,
        deskripsi TEXT NOT NULL,
        wilayah_id INT NULL,
        lokasi_tempat VARCHAR(200) NOT NULL,
        tanggal_mulai DATETIME NOT NULL,
        tanggal_selesai DATETIME NULL,
        status_tampil BOOLEAN NOT NULL DEFAULT TRUE,
        CONSTRAINT fk_agenda_wilayah FOREIGN KEY (wilayah_id) REFERENCES wilayah(id) ON UPDATE CASCADE ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ```

12. **`kontak_fkub`**
    ```sql
    CREATE TABLE IF NOT EXISTS kontak_fkub (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_sekretariat VARCHAR(150) NOT NULL,
        wilayah_id INT NULL,
        alamat TEXT NOT NULL,
        no_wa VARCHAR(20) NOT NULL,
        email VARCHAR(100) NULL,
        status_aktif BOOLEAN NOT NULL DEFAULT TRUE,
        CONSTRAINT fk_kontak_fkub_wilayah FOREIGN KEY (wilayah_id) REFERENCES wilayah(id) ON UPDATE CASCADE ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ```

### 2. Data Referensi Baku `kategori_isu` (5 Baris) & `kategori_media` (4 Baris)

**`KategoriIsuSeedData::getRows()`:**
1. `id: 1`, `nama_kategori: 'Pendirian Rumah Ibadah'`, `deskripsi: 'Potensi sengketa, keberatan warga, atau kendala administratif terkait pendirian, renovasi, dan alih fungsi rumah ibadah mengacu pada PBM No. 9 dan No. 8 Tahun 2006.'`
2. `id: 2`, `nama_kategori: 'Gesekan Antarumat/Ormas SARA'`, `deskripsi: 'Ketegangan horizontal antarindividu, kelompok masyarakat, atau organisasi kemasyarakatan yang bermuatan suku, agama, ras, dan antargolongan.'`
3. `id: 3`, `nama_kategori: 'Penyiaran Agama/Penistaan'`, `deskripsi: 'Polemik materi dakwah/ceramah provokatif, penyiaran agama kepada pemeluk agama lain yang menimbulkan keresahan, atau dugaan penodaan simbol keagamaan.'`
4. `id: 4`, `nama_kategori: 'Aliran Keagamaan Baru'`, `deskripsi: 'Kemunculan atau aktivitas kelompok/paham keagamaan baru yang menimbulkan keresahan sosial dan membutuhkan pembinaan serta dialog lintas pihak.'`
5. `id: 5`, `nama_kategori: 'Hoaks Transnasional Perbatasan'`, `deskripsi: 'Penyebaran informasi palsu (hoaks), ujaran kebencian, atau infiltrasi paham intoleran lintas batas negara di wilayah perbatasan Kalimantan Utara.'`

**`KategoriIsuSeedData::getKategoriMediaRows()`:**
1. `id: 1`, `nama_kategori: 'Moderasi Beragama'`, `slug: 'moderasi-beragama'`
2. `id: 2`, `nama_kategori: 'Ekoteologi'`, `slug: 'ekoteologi'`
3. `id: 3`, `nama_kategori: 'Regulasi PBM 2006'`, `slug: 'regulasi-pbm-2006'`
4. `id: 4`, `nama_kategori: 'Harmoni Perbatasan'`, `slug: 'harmoni-perbatasan'`

### 3. Data Referensi Baku 55 Kecamatan pada 5 Kabupaten/Kota se-Kalimantan Utara (`WilayahSeedData`)

Setiap elemen wajib memiliki `kode_wilayah`, `nama_kabupaten`, `nama_kecamatan`, `latitude_pusat`, `longitude_pusat`, dan `polygon_geojson`:

- **Kabupaten Bulungan (10 Kecamatan):**
  1. `65.01.01` | `Kabupaten Bulungan` | `Tanjung Palas` | `2.83333300` | `117.35000000`
  2. `65.01.02` | `Kabupaten Bulungan` | `Tanjung Palas Barat` | `2.70000000` | `117.11666700`
  3. `65.01.03` | `Kabupaten Bulungan` | `Tanjung Palas Utara` | `2.95000000` | `117.28333300`
  4. `65.01.04` | `Kabupaten Bulungan` | `Tanjung Palas Timur` | `2.76666700` | `117.65000000`
  5. `65.01.05` | `Kabupaten Bulungan` | `Tanjung Selor` | `2.83750000` | `117.36527800`
  6. `65.01.06` | `Kabupaten Bulungan` | `Tanjung Palas Tengah` | `2.93333300` | `117.48333300`
  7. `65.01.07` | `Kabupaten Bulungan` | `Peso` | `2.73333300` | `116.71666700`
  8. `65.01.08` | `Kabupaten Bulungan` | `Peso Hilir` | `2.78333300` | `116.95000000`
  9. `65.01.09` | `Kabupaten Bulungan` | `Sekatak` | `3.11666700` | `117.08333300`
  10. `65.01.10` | `Kabupaten Bulungan` | `Bunyu` | `3.46666700` | `117.83333300`
- **Kabupaten Malinau (15 Kecamatan - Lokus Piloting Awal):**
  11. `65.02.01` | `Kabupaten Malinau` | `Mentarang` | `3.48333300` | `116.48333300`
  12. `65.02.02` | `Kabupaten Malinau` | `Malinau Kota` | `3.58333300` | `116.63333300`
  13. `65.02.03` | `Kabupaten Malinau` | `Pujungan` | `2.61666700` | `115.65000000`
  14. `65.02.04` | `Kabupaten Malinau` | `Kayan Hilir` | `2.18333300` | `115.93333300`
  15. `65.02.05` | `Kabupaten Malinau` | `Kayan Hulu` | `1.81666700` | `115.23333300`
  16. `65.02.06` | `Kabupaten Malinau` | `Malinau Selatan` | `3.30000000` | `116.51666700`
  17. `65.02.07` | `Kabupaten Malinau` | `Malinau Utara` | `3.66666700` | `116.65000000`
  18. `65.02.08` | `Kabupaten Malinau` | `Malinau Barat` | `3.55000000` | `116.56666700`
  19. `65.02.09` | `Kabupaten Malinau` | `Sungai Boh` | `1.45000000` | `115.08333300`
  20. `65.02.10` | `Kabupaten Malinau` | `Kayan Selatan` | `1.66666700` | `115.11666700`
  21. `65.02.11` | `Kabupaten Malinau` | `Bahau Hulu` | `2.95000000` | `115.81666700`
  22. `65.02.12` | `Kabupaten Malinau` | `Mentarang Hulu` | `3.75000000` | `116.16666700`
  23. `65.02.13` | `Kabupaten Malinau` | `Malinau Selatan Hilir` | `3.41666700` | `116.60000000`
  24. `65.02.14` | `Kabupaten Malinau` | `Malinau Selatan Hulu` | `3.10000000` | `116.35000000`
  25. `65.02.15` | `Kabupaten Malinau` | `Sungai Tubu` | `3.26666700` | `116.10000000`
- **Kabupaten Nunukan (21 Kecamatan):**
  26. `65.03.01` | `Kabupaten Nunukan` | `Sebatik` | `4.13333300` | `117.86666700`
  27. `65.03.02` | `Kabupaten Nunukan` | `Nunukan` | `4.13333300` | `117.66666700`
  28. `65.03.03` | `Kabupaten Nunukan` | `Sembakung` | `3.81666700` | `117.16666700`
  29. `65.03.04` | `Kabupaten Nunukan` | `Lumbis` | `4.15000000` | `116.63333300`
  30. `65.03.05` | `Kabupaten Nunukan` | `Krayan` | `3.88333300` | `115.80000000`
  31. `65.03.06` | `Kabupaten Nunukan` | `Sebuku` | `4.05000000` | `117.15000000`
  32. `65.03.07` | `Kabupaten Nunukan` | `Krayan Selatan` | `3.71666700` | `115.76666700`
  33. `65.03.08` | `Kabupaten Nunukan` | `Sebatik Barat` | `4.15000000` | `117.78333300`
  34. `65.03.09` | `Kabupaten Nunukan` | `Nunukan Selatan` | `4.05000000` | `117.68333300`
  35. `65.03.10` | `Kabupaten Nunukan` | `Sebatik Timur` | `4.15000000` | `117.90000000`
  36. `65.03.11` | `Kabupaten Nunukan` | `Sebatik Utara` | `4.16666700` | `117.88333300`
  37. `65.03.12` | `Kabupaten Nunukan` | `Sebatik Tengah` | `4.13333300` | `117.83333300`
  38. `65.03.13` | `Kabupaten Nunukan` | `Sei Menggaris` | `4.23333300` | `117.43333300`
  39. `65.03.14` | `Kabupaten Nunukan` | `Tulin Onsoi` | `4.18333300` | `117.06666700`
  40. `65.03.15` | `Kabupaten Nunukan` | `Lumbis Ogong` | `4.26666700` | `116.45000000`
  41. `65.03.16` | `Kabupaten Nunukan` | `Sembakung Atulai` | `3.86666700` | `116.95000000`
  42. `65.03.17` | `Kabupaten Nunukan` | `Krayan Tengah` | `3.80000000` | `115.95000000`
  43. `65.03.18` | `Kabupaten Nunukan` | `Krayan Timur` | `3.95000000` | `115.96666700`
  44. `65.03.19` | `Kabupaten Nunukan` | `Krayan Barat` | `3.85000000` | `115.68333300`
  45. `65.03.20` | `Kabupaten Nunukan` | `Lumbis Pansiangan` | `4.31666700` | `116.26666700`
  46. `65.03.21` | `Kabupaten Nunukan` | `Lumbis Hulu` | `4.28333300` | `116.10000000`
- **Kabupaten Tana Tidung (5 Kecamatan):**
  47. `65.04.01` | `Kabupaten Tana Tidung` | `Sesayap` | `3.58333300` | `117.01666700`
  48. `65.04.02` | `Kabupaten Tana Tidung` | `Sesayap Hilir` | `3.56666700` | `117.28333300`
  49. `65.04.03` | `Kabupaten Tana Tidung` | `Tana Lia` | `3.68333300` | `117.65000000`
  50. `65.04.04` | `Kabupaten Tana Tidung` | `Betayau` | `3.43333300` | `117.06666700`
  51. `65.04.05` | `Kabupaten Tana Tidung` | `Muruk Rian` | `3.51666700` | `116.85000000`
- **Kota Tarakan (4 Kecamatan):**
  52. `65.71.01` | `Kota Tarakan` | `Tarakan Barat` | `3.31666700` | `117.58333300`
  53. `65.71.02` | `Kota Tarakan` | `Tarakan Tengah` | `3.31666700` | `117.61666700`
  54. `65.71.03` | `Kota Tarakan` | `Tarakan Timur` | `3.28333300` | `117.63333300`
  55. `65.71.04` | `Kota Tarakan` | `Tarakan Utara` | `3.38333300` | `117.56666700`

### 4. Kontrak JSON Endpoint Master Data

- **`GET /api/master/wilayah`** (opsional `?kabupaten=Kabupaten%20Malinau&include_geojson=1`):
  ```json
  {
    "statusCode": 200,
    "data": {
      "total": 55,
      "kabupaten_list": [
        "Kabupaten Bulungan",
        "Kabupaten Malinau",
        "Kabupaten Nunukan",
        "Kabupaten Tana Tidung",
        "Kota Tarakan"
      ],
      "items": [
        {
          "id": 1,
          "kode_wilayah": "65.01.01",
          "nama_kabupaten": "Kabupaten Bulungan",
          "nama_kecamatan": "Tanjung Palas",
          "latitude_pusat": 2.833333,
          "longitude_pusat": 117.35
        }
      ]
    }
  }
  ```
- **`GET /api/master/kategori-isu`**:
  ```json
  {
    "statusCode": 200,
    "data": [
      {
        "id": 1,
        "nama_kategori": "Pendirian Rumah Ibadah",
        "deskripsi": "Potensi sengketa, keberatan warga, atau kendala administratif terkait pendirian, renovasi, dan alih fungsi rumah ibadah mengacu pada PBM No. 9 dan No. 8 Tahun 2006."
      }
    ]
  }
  ```

## Testing

- **Command otomatis wajib:** `composer test` (PHPUnit), `vendor/bin/phpcs` (PSR-12), dan `vendor/bin/phpstan` (PHPStan Level 4).
- **Cakupan pengujian unit & action:**
  - `PdoConnectionFactoryTest`: verifikasi konfigurasi `'db'` pada `SettingsInterface` dan pembentukan DSN MySQL/SQLite.
  - `SchemaMigratorTest`: verifikasi keberadaan dan urutan ke-12 tabel di `SchemaDefinition::TABLE_ORDER`, kelengkapan kolom dan ENUM pada SQL DDL, serta eksekusi `migrate()` dan `dropAll()` pada mock `PDO`.
  - `KategoriIsuTest` & `PdoKategoriIsuRepositoryTest`: verifikasi serialisasi JSON `KategoriIsu`, kelengkapan 5 baris di `KategoriIsuSeedData`, serta query `findAll()` dan `findById()` (termasuk pelemparan `KategoriIsuNotFoundException`).
  - `WilayahTest`, `WilayahSeedDataTest`, `PdoWilayahRepositoryTest`, & `DatabaseSeederTest`: verifikasi tepat 55 kecamatan dan distribusi per Kabupaten/Kota (10 + 15 + 21 + 5 + 4 = 55), keunikan `kode_wilayah`, validitas struktur JSON GeoJSON `Polygon` pada ke-55 kecamatan, transaksi `seedAll()`, serta query `PdoWilayahRepository`.
  - `ListWilayahActionTest` & `ListKategoriIsuActionTest`: verifikasi respons HTTP 200, filter `?kabupaten=`, opsi `?include_geojson=1`, dan struktur JSON `ActionPayload`.

## Notes for the AI

- **Kompatibilitas PHP 8.4:** Selalu tulis tipe nullable secara eksplisit (`?string $polygonGeojson = null`, bukan `string $polygonGeojson = null`) dan tambahkan `#[\ReturnTypeWillChange]` pada `jsonSerialize(): array` mengikuti pola di `src/Domain/User/User.php`.
- **Inisialisasi `PDO` yang Lazy:** Di `app/dependencies.php`, daftarkan `PDO::class` sebagai closure `function (ContainerInterface $c): PDO { ... }` sehingga koneksi jaringan ke server MySQL hanya dibuka ketika `PDO::class` benar-benar diminta dari container. Pada pengujian Action (`ListWilayahActionTest`, `ListKategoriIsuActionTest`), ganti binding `WilayahRepository::class` dan `KategoriIsuRepository::class` di container dengan mock `Prophecy` (mengikuti pola `tests/Application/Actions/User/ListUserActionTest.php`), sehingga `composer test` selalu deterministik dan tidak bergantung pada daemon MySQL eksternal.
- **Jangan Merusak Rute atau Pengujian Existing:** Pertahankan rute `/users` dan seluruh class `User` bawaan scaffold sampai Fitur 2 menggantinya secara terstruktur, agar ke-19 test bawaan tetap hijau.


<!-- blueprint:completion {"schemaVersion":2,"specBytes":40790,"specSha256":"39f043ddb82cce7c88089663fa041693dca5b1aaa05456672dd18625cae6dab5","branch":"refs/heads/feature/skema-basis-data-mysql-master-data-wilayah-kaltara-tahap-1-m1","head":"26b469a190eb04124998683b3ebb5bd285de35fa","baseRef":"refs/heads/main","baseCommit":"26b469a190eb04124998683b3ebb5bd285de35fa","sourceTree":"530ba32777ece4b9c3765bd4256c2165cf6d84d9","landing":"local-merge","absentOptional":[]} -->

## Try it now

Jalankan skrip migrasi dan seeder master data via CLI:
```bash
php bin/migrate.php
php bin/seed.php
```

Atau lakukan request pengujian ke endpoint master data:
```bash
curl -s http://localhost:8080/api/master/wilayah
curl -s "http://localhost:8080/api/master/wilayah?kabupaten=Kabupaten%20Malinau&include_geojson=1"
curl -s http://localhost:8080/api/master/kategori-isu
```
