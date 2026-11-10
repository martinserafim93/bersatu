# Feature: Pendaftaran Mandiri, Verifikasi Akun & Autentikasi Multi-Role RBAC (Tahap 1 - M2)

**From build-plan:** feature 2
**Build attempt:** 1
**Branch:** feature/pendaftaran-mandiri-verifikasi-akun-autentikasi-multi-role-rbac-tahap-1-m2
**Status:** verified

## Goal

Membangun fondasi akun pengguna **Portal BERSATU** di atas tabel `users` yang sudah ada: pendaftaran mandiri (`masyarakat`, `penyuluh_ipari`, `kua`, `fkub`) dengan status `menunggu_verifikasi`, persetujuan/penolakan/koreksi peran/penonaktifan oleh `superadmin`, login-logout berbasis sesi PHP native, middleware otorisasi untuk 7 peran, serta layanan enkripsi identitas (`AES-256-GCM` + *blind index* `HMAC-SHA256`) lengkap dengan aturan siapa yang boleh melihat identitas asli. Semua dikerjakan sebagai **API JSON** (halaman HTML `/auth/login` dan `/auth/register` dibangun di Fitur 3 di atas API ini).

Aturan peran yang berlaku (sudah ditegaskan pengguna):

- `superadmin` **hanya mengelola akun**, konten, dan master data. Ia **tidak** mengakses atau mengelola laporan EWS dan **tidak** melihat identitas pelapor pada laporan.
- `verifikator` hanya melihat data pengaju dan mengelola data laporan. Ia tidak mengelola akun.
- Hanya satu penanda status akun: kolom `users.status_akun`. Login hanya boleh jika `status_akun = 'aktif'`.

## In scope

- Pemuat `.env` sederhana (`EnvLoader`) agar `composer start` dan skrip `bin/*.php` membaca `APP_ENCRYPTION_KEY` dan variabel DB dari berkas `.env` lokal. Variabel yang sudah ada di environment proses tidak ditimpa.
- Setting `security.app_key` di `app/settings.php` dan layanan `IdentityCipher` (enkripsi `AES-256-GCM`, dekripsi, *blind index* `HMAC-SHA256`, dua subkunci turunan dari satu kunci utama lewat `hash_hkdf`).
- Mengganti scaffold bawaan Slim `User` (nama depan/belakang, `InMemoryUserRepository`, rute `/users`) dengan domain `User` nyata yang memetakan tabel `users`: entitas, konstanta `Role` dan `StatusAkun`, `UserRepository`, `PdoUserRepository`.
- `AccountService`: registrasi publik, validasi, login, daftar akun untuk admin, setujui (dengan koreksi peran opsional), tolak, nonaktifkan, aktifkan kembali, pembuatan akun internal oleh `superadmin`, dan pembuatan `superadmin` pertama via CLI.
- `ReporterIdentityPolicy`: hanya `verifikator` dan pemilik akun yang boleh membuka identitas asli (NIP/NIK dan nomor WA terdekripsi).
- Sesi: `AuthSession`, `SessionMiddleware` (ditulis ulang), `AuthMiddleware`, `RoleMiddleware`, `RequireJsonMiddleware`.
- Endpoint: `POST /api/auth/register`, `POST /api/auth/login`, `POST /api/auth/logout`, `GET /api/auth/me`, `GET /api/admin/akun`, `POST /api/admin/akun`, `POST /api/admin/akun/{id}/setujui`, `.../tolak`, `.../nonaktifkan`, `.../aktifkan`.
- Skrip CLI `bin/create-superadmin.php` (satu-satunya jalan membuat `superadmin`).
- Pengujian PHPUnit untuk semua logika di atas, PHPCS PSR-12, PHPStan level 4.

## Out of scope

- Halaman HTML, formulir, `manifest.json`, Service Worker (Fitur 3). Tidak ada UI di fitur ini.
- Pembatasan percobaan login (*throttling*/*lockout*), lupa/ubah kata sandi, verifikasi email, notifikasi WhatsApp/email saat akun disetujui.
- Format penyamaran nama pelapor untuk `pimpinan` dan publik (ditentukan saat laporan ditampilkan, Fitur 4 dan 5). Fitur ini hanya menyediakan kebijakan boolean dan dekripsi.
- Endpoint laporan EWS, peta, Media Hub, Chatbot (Fitur 4 dan seterusnya).
- Antarmuka panel `/admin` dan CRUD master data (Fitur 9). Fitur ini hanya menyediakan API manajemen akun.
- Paginasi daftar akun, pengaturan durasi sesi, token CSRF berbasis form (lihat Notes).
- Mengubah skema tabel. Skema `users` dari Fitur 1 sudah final (`status_aktif` sudah dihapus pada commit `049a07c`).

## Build loop

- **Mode review langkah (`workflow.stepReview`):** `feature` - kerjakan dan verifikasi setiap langkah secara berurutan, lalu sajikan satu paket review lengkap di akhir fitur.
- **Komit checkpoint (`workflow.checkpointCommits`):** `disabled` - jangan membuat komit per langkah; komit fitur dibuat saat `/complete`.
- **Gate pengujian:** Setelah setiap langkah selesai, jalankan `composer test`, `vendor/bin/phpcs --standard=PSR12 src/ tests/ bin/ app/ public/`, dan `vendor/bin/phpstan analyse`. Seluruh perintah wajib lulus (`exit code 0`, tanpa error PHPCS) sebelum lanjut ke langkah berikutnya. Catatan: `composer test` mencetak peringatan `Deprecated` dari pustaka `php-di` dan satu *risky test* bawaan scaffold (`ActionTest`). Itu kondisi awal repo, bukan kegagalan.
- **Standar penulisan:** `declare(strict_types=1);` di setiap file PHP, tipe nullable eksplisit (`?string $x = null`), array pendek `[]`, indentasi 4 spasi (bukan tab), baris maksimal 120 karakter, dan **dilarang** memakai karakter *em dash*, *en dash*, atau *ellipsis* di kode, komentar, maupun dokumentasi (pakai tanda hubung biasa `-`).
- **Jangan mengubah skema.** Jika merasa perlu mengubah `SchemaDefinition.php`, berhenti dan laporkan.

## Build steps

- [x] **Step 1: `EnvLoader`, setting `security.app_key`, dan `IdentityCipher`**
  - **Tujuan:** Menyiapkan pembacaan `.env` dan layanan kriptografi identitas.
  - **Instruksi:**
    1. Buat `src/Infrastructure/Config/EnvLoader.php` (`namespace App\Infrastructure\Config`) berisi `final class EnvLoader` dengan satu method `public static function load(string $path): void`. Perilaku: jika `is_file($path)` salah, `return` tanpa error. Baca baris dengan `file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)`. Untuk tiap baris: `trim`, lewati jika kosong atau diawali `#`, lewati jika tidak ada `=`. Pisah pada `=` **pertama** menjadi `$key` dan `$value` (keduanya di-`trim`). Jika `$value` diapit sepasang `"` atau `'`, buang tanda kutip itu. Jika `$key === ''`, atau `getenv($key) !== false`, atau `isset($_ENV[$key])`, lewati (jangan menimpa). Selain itu jalankan `putenv("$key=$value")` dan `$_ENV[$key] = $value`.
    2. Panggil `EnvLoader::load(__DIR__ . '/../.env');` tepat setelah `require .../vendor/autoload.php` di `public/index.php`, `bin/migrate.php`, dan `bin/seed.php` (tambahkan `use App\Infrastructure\Config\EnvLoader;` di bagian `use`). **Jangan** panggil di `tests/`.
    3. Di `app/settings.php`, tambahkan di array `Settings` (sejajar dengan `'db'`): `'security' => ['app_key' => (string) ($_ENV['APP_ENCRYPTION_KEY'] ?? getenv('APP_ENCRYPTION_KEY') ?: '')],`.
    4. Buat `src/Infrastructure/Security/IdentityCipher.php` (`namespace App\Infrastructure\Security`), `final class IdentityCipher` dengan:
       - `public function __construct(string $hexKey)`: jika `preg_match('/^[0-9a-fA-F]{64}$/', $hexKey) !== 1` lempar `InvalidArgumentException('APP_ENCRYPTION_KEY harus 64 karakter heksadesimal (32 byte). Buat dengan: php -r "echo bin2hex(random_bytes(32));"')`. Lalu `$master = hex2bin($hexKey)`, `$this->encKey = hash_hkdf('sha256', $master, 32, 'bersatu-aes-256-gcm')`, `$this->indexKey = hash_hkdf('sha256', $master, 32, 'bersatu-blind-index')` (dua properti `private string`).
       - `public function encrypt(string $plain): string`: `$iv = random_bytes(12)`; `$cipher = openssl_encrypt($plain, 'aes-256-gcm', $this->encKey, OPENSSL_RAW_DATA, $iv, $tag, '', 16)`; jika `$cipher === false` lempar `RuntimeException('Enkripsi gagal.')`; `return base64_encode($iv . $tag . $cipher);`
       - `public function decrypt(string $payload): string`: `$raw = base64_decode($payload, true)`; jika `$raw === false` atau `strlen($raw) < 28` lempar `RuntimeException('Data terenkripsi tidak valid.')`; `$iv = substr($raw, 0, 12)`, `$tag = substr($raw, 12, 16)`, `$cipher = substr($raw, 28)`; `$plain = openssl_decrypt($cipher, 'aes-256-gcm', $this->encKey, OPENSSL_RAW_DATA, $iv, $tag)`; jika `$plain === false` lempar `RuntimeException('Data terenkripsi tidak valid.')`.
       - `public function blindIndex(string $plain): string`: `return hash_hmac('sha256', $plain, $this->indexKey);` (selalu 64 karakter heksadesimal huruf kecil, cocok dengan `CHAR(64)`).
    5. Daftarkan di `app/dependencies.php` (di array `addDefinitions`, sejajar dengan `PDO::class`): `IdentityCipher::class => function (ContainerInterface $c): IdentityCipher { /** @var SettingsInterface $settings */ $settings = $c->get(SettingsInterface::class); /** @var array<string, mixed> $security */ $security = $settings->get('security'); return new IdentityCipher((string) $security['app_key']); },` plus `use App\Infrastructure\Security\IdentityCipher;`. Closure ini hanya jalan saat `IdentityCipher` diminta, sehingga endpoint lain tidak terganggu bila kunci belum diisi.
    6. Di `.env.example`, ganti baris `APP_ENCRYPTION_KEY=...` menjadi dua baris: komentar `# 64 karakter heksadesimal. Buat dengan: php -r "echo bin2hex(random_bytes(32));"` lalu `APP_ENCRYPTION_KEY=` (nilai kosong agar gagal cepat bila lupa diisi). Jangan mengubah variabel lain.
  - **Tes:** `tests/Infrastructure/Config/EnvLoaderTest.php` (tulis berkas `.env` sementara di `sys_get_temp_dir()`; **wajib** mengembalikan `$_ENV` dan `putenv` ke semula di blok `finally`, karena `phpunit.xml` memakai `beStrictAboutChangesToGlobalState`): memuat pasangan sederhana, mengabaikan komentar dan baris kosong, membuang kutip, tidak menimpa variabel yang sudah ada, berkas tidak ada tidak error. `tests/Infrastructure/Security/IdentityCipherTest.php` memakai kunci uji tetap `str_repeat('ab', 32)`: (a) `decrypt(encrypt('3174012345678901'))` mengembalikan teks asal; (b) dua kali `encrypt` pada teks sama menghasilkan keluaran berbeda (IV acak); (c) `blindIndex` deterministik, panjang 64, dan berbeda untuk input berbeda; (d) memodifikasi satu karakter payload membuat `decrypt` melempar `RuntimeException`; (e) payload terlalu pendek melempar `RuntimeException`; (f) kunci pendek, bukan heksadesimal, atau kosong melempar `InvalidArgumentException`; (g) kunci berbeda tidak bisa mendekripsi payload kunci lain.
  - **Done when:** `composer test`, `vendor/bin/phpcs ...`, dan `vendor/bin/phpstan analyse` lulus; semua tes baru di atas hijau; ke-45 tes lama tetap hijau.

- [x] **Step 2: Domain `User` nyata (menggantikan scaffold) dan `PdoUserRepository`**
  - **Tujuan:** Domain akun yang memetakan tabel `users`, tanpa menyisakan scaffold lama.
  - **Instruksi:**
    1. **Hapus** scaffold: `src/Application/Actions/User/ListUsersAction.php`, `ViewUserAction.php`, `UserAction.php`, `src/Infrastructure/Persistence/User/InMemoryUserRepository.php`, serta tes `tests/Application/Actions/User/ListUserActionTest.php`, `ViewUserActionTest.php`, `tests/Domain/User/UserTest.php`, `tests/Infrastructure/Persistence/User/InMemoryUserRepositoryTest.php`. Di `app/routes.php` hapus `use ...ListUsersAction`, `use ...ViewUserAction`, dan seluruh blok `$app->group('/users', ...)`. Di `app/repositories.php` ganti binding `UserRepository::class => \DI\autowire(InMemoryUserRepository::class)` menjadi `\DI\autowire(PdoUserRepository::class)` (ubah `use` dan hapus komentar lama tentang in memory).
    2. `src/Domain/User/Role.php`: `final class Role` berisi konstanta string `SUPERADMIN = 'superadmin'`, `PIMPINAN = 'pimpinan'`, `VERIFIKATOR = 'verifikator'`, `PENYULUH_IPARI = 'penyuluh_ipari'`, `KUA = 'kua'`, `FKUB = 'fkub'`, `MASYARAKAT = 'masyarakat'`, dan konstanta array: `PUBLIC_REGISTRATION = [PENYULUH_IPARI, KUA, FKUB, MASYARAKAT]`, `FIELD_REPORTERS = [PENYULUH_IPARI, KUA, FKUB]`, `ADMIN_CREATABLE = [VERIFIKATOR, PIMPINAN, PENYULUH_IPARI, KUA, FKUB, MASYARAKAT]`, `ALL` (ketujuh peran, urutan sama dengan ENUM DB: superadmin, pimpinan, verifikator, penyuluh_ipari, kua, fkub, masyarakat).
    3. `src/Domain/User/StatusAkun.php`: `final class StatusAkun` dengan konstanta `MENUNGGU_VERIFIKASI = 'menunggu_verifikasi'`, `AKTIF = 'aktif'`, `DITOLAK = 'ditolak'`, `NONAKTIF = 'nonaktif'`, dan `ALL` (empat nilai).
    4. Timpa `src/Domain/User/User.php` (`final class User implements JsonSerializable`) dengan konstruktor promosi properti `private`: `int $id`, `string $namaLengkap`, `?string $instansi`, `string $email`, `string $passwordHash`, `string $role`, `int $wilayahId`, `string $statusAkun`, `string $nipNikEncrypted`, `string $noWaEncrypted`, `string $createdAt`. Buat getter untuk semuanya (`getId()`, `getNamaLengkap()`, `getInstansi()`, `getEmail()`, `getPasswordHash()`, `getRole()`, `getWilayahId()`, `getStatusAkun()`, `getNipNikEncrypted()`, `getNoWaEncrypted()`, `getCreatedAt()`). `#[\ReturnTypeWillChange] public function jsonSerialize(): array` mengembalikan **hanya**: `id`, `nama_lengkap`, `instansi`, `email`, `role`, `wilayah_id`, `status_akun`, `created_at`. **Tidak boleh** memuat `passwordHash`, `nipNikEncrypted`, atau `noWaEncrypted`.
    5. `src/Domain/User/NewUser.php`: `final class NewUser` dengan konstruktor promosi properti `public readonly`: `string $nipNikEncrypted`, `string $nipNikHash`, `string $namaLengkap`, `?string $instansi`, `string $noWaEncrypted`, `string $email`, `string $passwordHash`, `string $role`, `int $wilayahId`, `string $statusAkun`.
    6. `src/Domain/User/DuplicateUserException.php` (`extends DomainException`, `public $message = 'Email atau NIP/NIK sudah terdaftar.'`). Timpa `UserNotFoundException` (`extends DomainRecordNotFoundException`, `public $message = 'Akun pengguna yang diminta tidak ditemukan.'`).
    7. Timpa `src/Domain/User/UserRepository.php`:
       - `findById(int $id): User` (`@throws UserNotFoundException`)
       - `findByEmail(string $email): ?User`
       - `findByStatus(?string $status): array` (`@return array<int, User>`; `null` = semua status; urut `created_at ASC, id ASC`)
       - `create(NewUser $user): int` (`@throws DuplicateUserException`; mengembalikan id baru)
       - `transitionStatus(int $id, string $from, string $to, ?string $role = null): bool` (UPDATE bersyarat; `true` bila tepat satu baris berubah)
    8. `src/Infrastructure/Persistence/User/PdoUserRepository.php` (`final class`, konstruktor `PDO $pdo`). Konstanta kolom `private const COLUMNS = 'id, nip_nik_encrypted, nama_lengkap, instansi, no_wa_encrypted, email, password_hash, role, wilayah_id, status_akun, created_at'` (sengaja **tanpa** `nip_nik_hash`, karena entitas `User` tidak memuatnya). Gunakan `prepare` + `execute` dengan parameter bernama, ikuti pola `PdoWilayahRepository`. Pemetaan baris ke `User`: cast `(int)` untuk `id` dan `wilayah_id`, `instansi` boleh `null`.
       - `findById`: `SELECT <COLUMNS> FROM users WHERE id = :id LIMIT 1`; baris kosong -> `UserNotFoundException`.
       - `findByEmail`: `... WHERE email = :email LIMIT 1`; kosong -> `null`.
       - `findByStatus`: tanpa filter `SELECT <COLUMNS> FROM users ORDER BY created_at ASC, id ASC` (pakai `query`); dengan filter `... WHERE status_akun = :status ORDER BY created_at ASC, id ASC`.
       - `create`: `INSERT INTO users (nip_nik_encrypted, nip_nik_hash, nama_lengkap, instansi, no_wa_encrypted, email, password_hash, role, wilayah_id, status_akun) VALUES (:nip_nik_encrypted, :nip_nik_hash, :nama_lengkap, :instansi, :no_wa_encrypted, :email, :password_hash, :role, :wilayah_id, :status_akun)`. Bungkus `execute` dengan `try/catch (PDOException $e)`: jika `($e->errorInfo[0] ?? '') === '23000'` lempar `DuplicateUserException`, selain itu lempar ulang. Kembalikan `(int) $this->pdo->lastInsertId()`.
       - `transitionStatus`: `UPDATE users SET status_akun = :to, role = COALESCE(:role, role) WHERE id = :id AND status_akun = :from`; `return $stmt->rowCount() === 1;`.
    9. Pastikan tidak ada lagi referensi ke `InMemoryUserRepository`, `ListUsersAction`, `ViewUserAction`, atau `UserAction` di `app/`, `src/`, `tests/` (cek dengan pencarian teks).
  - **Tes:** `tests/Domain/User/UserTest.php` (baru): `jsonSerialize` memuat tepat 8 kunci di atas dan **tidak** memuat `password`, `hash`, atau `encrypted` di kunci maupun nilai. `tests/Domain/User/RoleTest.php`: `PUBLIC_REGISTRATION` tepat 4 peran dan tidak memuat `superadmin`, `pimpinan`, `verifikator`; `ALL` berisi 7 nilai unik; `ADMIN_CREATABLE` tidak memuat `superadmin`. `tests/Infrastructure/Persistence/User/PdoUserRepositoryTest.php` (Prophecy untuk `PDO` dan `PDOStatement`, ikuti `PdoWilayahRepositoryTest`): `findById` terpetakan benar, `findById` tidak ada -> `UserNotFoundException`, `findByEmail` tidak ada -> `null`, `findByStatus` dengan dan tanpa filter, `create` sukses mengembalikan id, `create` dengan `PDOException` ber-`errorInfo[0] = '23000'` -> `DuplicateUserException`, `create` dengan error lain dilempar ulang, `transitionStatus` `true` untuk `rowCount() === 1` dan `false` untuk `0`.
  - **Done when:** gate lulus; `grep` teks `InMemoryUserRepository`/`ListUsersAction` di `app src tests` tidak menemukan apa pun; rute `/users` tidak ada lagi; jumlah tes naik atau turun sesuai perubahan di atas namun semuanya hijau.

- [x] **Step 3: `AccountService` - validasi dan pendaftaran publik**
  - **Tujuan:** Logika pendaftaran mandiri dengan validasi lengkap.
  - **Instruksi:**
    1. Buat `src/Domain/User/ValidationException.php` (`extends DomainException`): konstruktor `(array $errors)` menyimpan `array<string, string>` (`getErrors()`), pesan bawaan `'Data yang dikirim tidak valid.'`.
    2. Buat `src/Domain/User/AccountService.php` (`class AccountService`, **bukan** `final`), konstruktor `(UserRepository $users, WilayahRepository $wilayah, IdentityCipher $cipher)`.
    3. `public function register(array $input): int`: panggil `$this->createAccount($input, $input['role'] ?? null, Role::PUBLIC_REGISTRATION, StatusAkun::MENUNGGU_VERIFIKASI)`.
    4. `private function createAccount(array $input, mixed $role, array $allowedRoles, string $status): int` melakukan validasi, mengumpulkan **semua** galat ke `array<string,string> $errors`, lalu bila tidak kosong `throw new ValidationException($errors)`. Aturan (pesan galat persis seperti tertulis):
       - `role`: harus string di dalam `$allowedRoles`, selain itu `'Peran tidak valid.'`.
       - `nama_lengkap`: `trim`, wajib, `mb_strlen <= 150`; galat `'Nama lengkap wajib diisi (maksimal 150 karakter).'`.
       - `instansi`: `trim`. Wajib (maksimal 150) bila `role` termasuk `Role::FIELD_REPORTERS`; galat `'Instansi/lembaga wajib diisi (maksimal 150 karakter).'`. Untuk peran lain: boleh kosong; bila diisi maksimal 150; kosong disimpan `null`. Untuk `masyarakat` nilai selalu disimpan `null` (abaikan isian).
       - `nip_nik`: `trim`, hanya digit. `masyarakat` harus `^\d{16}$` (NIK); peran lain `^(\d{16}|\d{18})$` (NIK 16 digit atau NIP 18 digit). Galat `'NIK harus 16 digit angka.'` untuk `masyarakat`, `'NIP (18 digit) atau NIK (16 digit) harus berupa angka.'` untuk lainnya.
       - `no_wa`: normalisasi dengan `private static function normalizeWa(string $raw): ?string`: hapus spasi, `-`, `.`, `(`, `)`; awalan `+62` diganti `62`; awalan `0` diganti `62`; selain itu harus sudah diawali `62`; hasil harus cocok `^62\d{8,13}$`, jika tidak `null`. Galat `'Nomor WhatsApp tidak valid.'`. Yang disimpan terenkripsi adalah bentuk ternormalisasi.
       - `email`: `trim` + `mb_strtolower`, lolos `filter_var(..., FILTER_VALIDATE_EMAIL)` dan `mb_strlen <= 100`; galat `'Email tidak valid.'`.
       - `password`: string, `mb_strlen >= 8` dan `strlen <= 72` (batas byte bcrypt agar tidak terpotong diam-diam); galat `'Kata sandi minimal 8 karakter dan maksimal 72 byte.'`. **Jangan** `trim` kata sandi.
       - `wilayah_id`: integer positif (terima `int` atau string digit), lalu `$this->wilayah->findById($id)`; bila `WilayahNotFoundException` galat `'Wilayah tidak ditemukan.'`; bila bukan integer positif galat `'Wilayah wajib dipilih.'`.
       - Nilai bukan string pada field teks dianggap kosong (tidak boleh menimbulkan `TypeError`).
    5. Setelah valid: bangun `NewUser` dengan `nipNikEncrypted = $cipher->encrypt($nipNik)`, `nipNikHash = $cipher->blindIndex($nipNik)`, `noWaEncrypted = $cipher->encrypt($waNormal)`, `passwordHash = password_hash($password, PASSWORD_DEFAULT)`, lalu `return $this->users->create($newUser);`. `DuplicateUserException` dibiarkan naik ke pemanggil.
  - **Tes:** `tests/Support/FakeUserRepository.php` (`namespace Tests\Support`, mengimplementasikan `UserRepository` dengan array di memori; `create` melempar `DuplicateUserException` jika email atau `nipNikHash` sudah ada; `transitionStatus` meniru perilaku `UPDATE ... WHERE status_akun = :from`). `tests/Domain/User/AccountServiceRegisterTest.php` memakai fake itu, `IdentityCipher` berkunci uji, dan Prophecy untuk `WilayahRepository`: pendaftaran valid tiap 4 peran menyimpan status `menunggu_verifikasi`, email ter-lowercase, NIK dan WA tersimpan **terenkripsi** (nilai tersimpan tidak sama dengan teks asal tetapi terdekripsi kembali dengan benar, WA tersimpan sebagai `62...`), `password_verify` benar terhadap hash tersimpan, `masyarakat` menyimpan `instansi = null`; data provider untuk setiap aturan galat (peran `superadmin`/`verifikator`/`pimpinan`/kosong/bukan string ditolak, NIK 15 digit, NIK berhuruf, NIP 18 digit ditolak untuk `masyarakat` tetapi diterima `kua`, WA `0812-3456-7890` dan `+6281234567890` dinormalisasi sama, WA `123` ditolak, email salah, sandi 7 karakter, sandi 73 byte, instansi kosong ditolak untuk `kua`, wilayah tidak ada); semua galat terkumpul sekaligus dalam satu `ValidationException`; email atau NIK ganda -> `DuplicateUserException`.
  - **Done when:** gate lulus; tes registrasi di atas hijau.

- [x] **Step 4: Login (`authenticate`) dan `ReporterIdentityPolicy`**
  - **Tujuan:** Verifikasi kredensial yang aman dan aturan visibilitas identitas.
  - **Instruksi:**
    1. Buat `src/Domain/User/AuthenticationFailedException.php` (`extends DomainException`, `$message = 'Email atau kata sandi salah.'`) dan `src/Domain/User/AccountNotActiveException.php` (`extends DomainException`; konstruktor `(string $statusAkun)` memilih pesan: `menunggu_verifikasi` -> `'Akun Anda masih menunggu verifikasi superadmin.'`, `ditolak` -> `'Pendaftaran akun Anda ditolak. Hubungi pengelola portal.'`, `nonaktif` -> `'Akun Anda dinonaktifkan. Hubungi pengelola portal.'`; sediakan `getStatusAkun()`).
    2. Tambahkan ke `AccountService`: `public function authenticate(mixed $email, mixed $password): User`. Jika salah satu bukan string atau kosong -> `AuthenticationFailedException`. `$user = $this->users->findByEmail(mb_strtolower(trim($email)))`. **Selalu** jalankan `password_verify` agar waktu respons tidak membocorkan keberadaan email: bila `$user === null` verifikasi terhadap hash dummy `self::dummyHash()` (method `private static` yang memanggil `password_hash('dummy-password', PASSWORD_DEFAULT)` sekali lalu menyimpannya di properti `private static ?string`). Bila verifikasi gagal atau `$user === null` -> `AuthenticationFailedException`. Hanya setelah kata sandi benar, bila `$user->getStatusAkun() !== StatusAkun::AKTIF` -> `AccountNotActiveException($user->getStatusAkun())`. Lolos -> `return $user`.
    3. Buat `src/Domain/User/IdentityAccessDeniedException.php` (`extends DomainException`, `$message = 'Anda tidak berhak melihat identitas ini.'`) dan `src/Domain/User/ReporterIdentityPolicy.php` (konstruktor `IdentityCipher $cipher`):
       - `canViewRealIdentity(User $viewer, User $owner): bool`: `true` hanya jika `$viewer->getStatusAkun() === StatusAkun::AKTIF` **dan** (`$viewer->getRole() === Role::VERIFIKATOR` **atau** `$viewer->getId() === $owner->getId()`). `superadmin`, `pimpinan`, peran lain, dan publik selalu `false`.
       - `reveal(User $viewer, User $owner): array`: bila tidak boleh lempar `IdentityAccessDeniedException`; bila boleh kembalikan `['nip_nik' => $cipher->decrypt(...), 'no_wa' => $cipher->decrypt(...)]`.
  - **Tes:** `tests/Domain/User/AccountServiceAuthenticateTest.php`: akun aktif + sandi benar -> `User`; sandi salah -> `AuthenticationFailedException`; email tidak ada -> `AuthenticationFailedException` (pesan sama persis); akun `menunggu_verifikasi`/`ditolak`/`nonaktif` dengan sandi **benar** -> `AccountNotActiveException` berstatus sesuai dan pesan berbeda per status; akun tidak aktif dengan sandi **salah** -> `AuthenticationFailedException` (tidak membocorkan status); email beda kapitalisasi dan spasi tetap cocok; input bukan string/kosong -> `AuthenticationFailedException`. `tests/Domain/User/ReporterIdentityPolicyTest.php`: `verifikator` aktif boleh; pemilik akun boleh; `superadmin`, `pimpinan`, `masyarakat` lain, `penyuluh_ipari` lain tidak boleh; `verifikator` berstatus `nonaktif` tidak boleh; `reveal` mengembalikan NIK dan WA asli untuk yang berhak dan melempar `IdentityAccessDeniedException` untuk yang tidak.
  - **Done when:** gate lulus; tes login dan kebijakan hijau.

- [x] **Step 5: Manajemen akun oleh `superadmin` di `AccountService`**
  - **Tujuan:** Daftar, setujui, tolak, nonaktifkan, aktifkan, buat akun internal, dan buat `superadmin`.
  - **Instruksi:**
    1. Buat `src/Domain/User/AccountConflictException.php` (`extends DomainException`; pesan diberikan pemanggil).
    2. Tambahkan ke `AccountService`:
       - `listAccounts(?string $status): array`: jika `$status !== null` dan tidak ada di `StatusAkun::ALL` lempar `ValidationException(['status' => 'Status tidak valid.'])`. Kembalikan `array<int, array<string, mixed>>`, tiap elemen `$user->jsonSerialize()` ditambah `nip_nik` dan `no_wa` hasil `decrypt` (superadmin memerlukannya untuk memeriksa pendaftar; dekripsi hanya terjadi di jalur admin ini).
       - `approve(int $id, ?string $correctedRole = null): void`: transisi ke `aktif` dari `menunggu_verifikasi` atau `ditolak`.
       - `reject(int $id): void`: `menunggu_verifikasi` -> `ditolak`.
       - `deactivate(int $id): void`: `aktif` -> `nonaktif`.
       - `reactivate(int $id): void`: `nonaktif` -> `aktif`.
       - Implementasi bersama `private function transition(int $id, array $allowedFrom, string $to, ?string $correctedRole = null): void`: (a) `$user = $this->users->findById($id)` (melempar `UserNotFoundException` -> 404); (b) jika `$user->getRole() === Role::SUPERADMIN` lempar `AccountConflictException('Akun superadmin tidak dapat diubah melalui antarmuka.')` (ini juga mencegah superadmin mengunci dirinya sendiri); (c) jika status sekarang tidak ada di `$allowedFrom` lempar `AccountConflictException('Status akun saat ini tidak mengizinkan tindakan ini.')`; (d) bila `$correctedRole !== null`: harus di `Role::PUBLIC_REGISTRATION` kalau tidak `ValidationException(['role' => 'Peran koreksi tidak valid.'])`; peran akun sekarang juga harus di `Role::PUBLIC_REGISTRATION` kalau tidak `AccountConflictException('Peran akun internal tidak dapat dikoreksi.')`; bila peran koreksi termasuk `Role::FIELD_REPORTERS` dan `getInstansi()` kosong lempar `ValidationException(['role' => 'Peran ini memerlukan instansi pada akun.'])`; (e) panggil `$this->users->transitionStatus($id, $user->getStatusAkun(), $to, $correctedRole)`; bila `false` lempar `AccountConflictException('Status akun sudah berubah. Muat ulang data.')`. Koreksi peran hanya diteruskan oleh `approve`.
       - `createInternal(array $input): int`: `createAccount($input, $input['role'] ?? null, Role::ADMIN_CREATABLE, StatusAkun::AKTIF)`.
       - `createSuperadmin(array $input): int`: `createAccount($input, Role::SUPERADMIN, [Role::SUPERADMIN], StatusAkun::AKTIF)`. Aturan `instansi` untuk `superadmin` mengikuti "peran lain" (opsional).
  - **Tes:** `tests/Domain/User/AccountServiceAdminTest.php` (fake repository): setujui dari `menunggu_verifikasi` dan dari `ditolak` -> `aktif`; setujui dengan koreksi `masyarakat` -> `kua` (instansi ada) berhasil dan peran berubah; koreksi ke peran internal (`verifikator`) -> `ValidationException`; koreksi `masyarakat` -> `kua` saat `instansi` null -> `ValidationException`; koreksi pada akun `verifikator` -> `AccountConflictException`; tolak hanya dari `menunggu_verifikasi`; tolak akun `aktif` -> konflik; nonaktifkan hanya dari `aktif`; aktifkan hanya dari `nonaktif`; semua aksi pada akun `superadmin` -> konflik; id tidak ada -> `UserNotFoundException`; `transitionStatus` yang mengembalikan `false` (status berubah di tengah jalan) -> konflik; `listAccounts` dengan dan tanpa filter, filter invalid -> `ValidationException`, hasilnya memuat `nip_nik` dan `no_wa` terdekripsi serta **tidak** memuat `password_hash`; `createInternal` membuat `verifikator` berstatus `aktif`, menolak peran `superadmin` dan peran tidak dikenal; `createSuperadmin` membuat `superadmin` aktif yang bisa lolos `authenticate`.
  - **Done when:** gate lulus; tes manajemen akun hijau.

- [x] **Step 6: Sesi dan middleware (`AuthSession`, `SessionMiddleware`, `AuthMiddleware`, `RoleMiddleware`, `RequireJsonMiddleware`)**
  - **Tujuan:** Autentikasi sesi dan otorisasi 7 peran di lapisan HTTP.
  - **Instruksi:**
    1. `src/Application/Auth/AuthSession.php`: `final class AuthSession`, konstruktor `(array &$data, callable $regenerateId, callable $destroy)` (simpan `$data` sebagai referensi `private array $data` dengan `$this->data = &$data;`). Method: `userId(): ?int` (kembalikan `(int) $this->data['user_id']` bila ada dan `is_int`/numerik, selain itu `null`); `login(int $userId): void` (panggil `($this->regenerateId)()` **sebelum** menulis `$this->data['user_id'] = $userId;` untuk mencegah *session fixation*); `logout(): void` (`$this->data = [];` lalu `($this->destroy)()`).
    2. Tulis ulang `src/Application/Middleware/SessionMiddleware.php` (hapus logika `HTTP_AUTHORIZATION` bawaan scaffold). `process`: jika atribut `session` pada request sudah `instanceof AuthSession` langsung `return $handler->handle($request)` (titik injeksi untuk tes). Jika tidak: bila `session_status() === PHP_SESSION_NONE`, panggil `session_name('BERSATUSESS')`, `ini_set('session.use_strict_mode', '1')`, `session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $isHttps, 'httponly' => true, 'samesite' => 'Lax'])` dengan `$isHttps = (($_SERVER['HTTPS'] ?? 'off') !== 'off')`, lalu `session_start()`. Buat `new AuthSession($_SESSION, static fn () => session_regenerate_id(true), static fn () => session_destroy())`, pasang sebagai atribut `session`, teruskan ke handler, dan setelah respons kembali panggil `session_write_close()` bila sesi aktif. Middleware ini tetap terdaftar global di `app/middleware.php` (tidak perlu diubah).
    3. `src/Application/Middleware/AuthMiddleware.php` (konstruktor `UserRepository $users`): ambil `$session = $request->getAttribute('session')`; bila bukan `AuthSession` atau `userId()` `null` -> respons `401` `ActionError::UNAUTHENTICATED` `'Silakan masuk terlebih dahulu.'`. Muat pengguna dengan `findById`; bila `UserNotFoundException` -> `$session->logout()` lalu `401` pesan sama. Bila `getStatusAkun() !== StatusAkun::AKTIF` -> `$session->logout()` lalu `401` `'Sesi berakhir karena status akun berubah.'` (penonaktifan berlaku seketika karena pengguna dimuat ulang tiap request). Lolos -> `withAttribute('user', $user)`.
    4. `src/Application/Middleware/RoleMiddleware.php` (konstruktor `array $allowedRoles`): ambil atribut `user`; bila bukan `User` -> `401` `UNAUTHENTICATED`; bila `getRole()` tidak ada di `$allowedRoles` -> `403` `ActionError::INSUFFICIENT_PRIVILEGES` `'Anda tidak memiliki akses ke sumber daya ini.'`.
    5. `src/Application/Middleware/RequireJsonMiddleware.php`: untuk metode selain `GET`, `HEAD`, `OPTIONS`, header `Content-Type` harus diawali `application/json` (bandingkan huruf kecil, abaikan `; charset=...`), kalau tidak respons `415` `ActionError::BAD_REQUEST` `'Content-Type harus application/json.'`. Ini bersama cookie `SameSite=Lax` adalah pertahanan CSRF untuk API ini (form lintas situs tidak bisa mengirim `application/json`, dan handler `OPTIONS` tidak mengirim header CORS sehingga *preflight* gagal di browser).
    6. Semua respons galat middleware dibuat dengan `new \Slim\Psr7\Response($status)`, isi `json_encode(new ActionPayload($status, null, new ActionError(...)), JSON_PRETTY_PRINT)`, header `Content-Type: application/json`. Buat satu helper `private function json(...)` per kelas atau trait kecil `src/Application/Middleware/JsonErrorResponse.php` untuk menghindari duplikasi.
  - **Tes:** `tests/Application/Middleware/*Test.php` memanggil `process()` langsung dengan handler palsu (anonymous class `RequestHandlerInterface` yang mengembalikan `Slim\Psr7\Response` 200 dan merekam request): `AuthSession` (`login` memanggil `regenerateId` sebelum menulis dan menyimpan id, `logout` mengosongkan dan memanggil `destroy`, `userId` `null` bila kosong); `AuthMiddleware` (tanpa sesi 401; id tidak ada di repo 401 dan sesi di-`logout`; akun `nonaktif` 401 dan di-`logout`; akun `aktif` lolos dan atribut `user` terpasang); `RoleMiddleware` (peran diizinkan lolos; peran lain 403; tanpa atribut `user` 401; uji ketujuh peran terhadap daftar `['superadmin']` dan `['verifikator']` dan `['pimpinan', 'verifikator']`); `RequireJsonMiddleware` (POST tanpa/ber-`text/html` 415; POST `application/json; charset=utf-8` lolos; GET lolos tanpa header).
  - **Done when:** gate lulus; tes middleware hijau.

- [x] **Step 7: Endpoint autentikasi (`register`, `login`, `logout`, `me`)**
  - **Tujuan:** Mengekspos pendaftaran dan login sebagai API JSON.
  - **Instruksi:**
    1. `src/Application/Actions/Auth/AccountAction.php`: `abstract class AccountAction extends Action` dengan konstruktor `(LoggerInterface $logger, protected AccountService $accounts)`. Ia menimpa `protected function action(): Response` (final) dengan `try { return $this->handle(); } catch (...)` dan deklarasi `abstract protected function handle(): Response;`. Pemetaan galat: `ValidationException` -> `respondWithData(['errors' => $e->getErrors()], 422)`; `AuthenticationFailedException` -> `respond(new ActionPayload(401, null, new ActionError(ActionError::UNAUTHENTICATED, $e->getMessage())))`; `AccountNotActiveException` -> 403 `ActionError::VERIFICATION_ERROR` dengan `$e->getMessage()`; `AccountConflictException` dan `DuplicateUserException` -> 409 `ActionError::NOT_ALLOWED` dengan `$e->getMessage()`. `UserNotFoundException` dibiarkan ke `Action::__invoke` (sudah menjadi 404). Tambahkan helper `protected function bodyArray(): array` yang mengembalikan `getParsedBody()` bila `array`, selain itu melempar `HttpBadRequestException($this->request, 'Body harus berupa objek JSON.')`.
    2. `RegisterAction` (`POST /api/auth/register`): `$id = $this->accounts->register($this->bodyArray());` -> `respondWithData(['id' => $id, 'status_akun' => StatusAkun::MENUNGGU_VERIFIKASI, 'pesan' => 'Pendaftaran diterima. Akun akan aktif setelah diverifikasi superadmin.'], 201)`. **Tidak** membuat sesi.
    3. `LoginAction` (`POST /api/auth/login`): ambil `email` dan `password` dari body (`$body['email'] ?? null`), panggil `authenticate`, ambil `AuthSession` dari atribut `session` (bila bukan `AuthSession` lempar `RuntimeException`), `$session->login($user->getId())`, `respondWithData(['user' => $user])`.
    4. `LogoutAction` (`POST /api/auth/logout`, wajib `AuthMiddleware`): `$session->logout()`; `respondWithData(['pesan' => 'Anda telah keluar.'])`.
    5. `MeAction` (`GET /api/auth/me`, wajib `AuthMiddleware`): `respondWithData(['user' => $this->request->getAttribute('user')])`.
    6. `app/routes.php`: tambah `$app->group('/api/auth', function (Group $group) { $group->post('/register', RegisterAction::class); $group->post('/login', LoginAction::class); $group->post('/logout', LogoutAction::class)->add(AuthMiddleware::class); $group->get('/me', MeAction::class)->add(AuthMiddleware::class); })->add(RequireJsonMiddleware::class);`.
  - **Tes:** `tests/Application/Actions/Auth/*Test.php` memakai `getAppInstance()`; set di container `UserRepository::class` = `FakeUserRepository`, `WilayahRepository::class` = mock Prophecy (`findById` mengembalikan `Wilayah`), dan `IdentityCipher::class` = instance berkunci uji. Request dibuat dengan `createRequest('POST', '/api/auth/register', ['HTTP_ACCEPT' => 'application/json', 'Content-Type' => 'application/json'])->withParsedBody([...])` (catatan: `getAppInstance()` tidak memasang `BodyParsingMiddleware`, jadi body harus diisi dengan `withParsedBody`). Untuk rute ber-`AuthMiddleware`, pasang atribut `session` berisi `AuthSession` di atas array uji dengan `withAttribute('session', ...)` dan callable no-op. Skenario: register sukses 201 dan akun tersimpan `menunggu_verifikasi`; register invalid 422 dengan `data.errors`; register ganda 409; register tanpa `Content-Type` JSON 415; register dengan `role` `superadmin` 422; login sukses 200 dan `AuthSession::userId()` terisi; login sandi salah 401; login akun `menunggu_verifikasi` 403 bertipe `VERIFICATION_ERROR`; login body bukan objek 400; `logout` tanpa sesi 401 dan dengan sesi 200 serta sesi kosong; `me` tanpa sesi 401 dan dengan sesi 200 dengan JSON **tanpa** kunci `password_hash`, `nip_nik_encrypted`, `no_wa_encrypted`.
  - **Done when:** gate lulus; tes endpoint autentikasi hijau.

- [x] **Step 8: Endpoint manajemen akun `superadmin`**
  - **Tujuan:** API admin untuk verifikasi dan pengelolaan akun.
  - **Instruksi:**
    1. Di `src/Application/Actions/Admin/` buat (semua `extends AccountAction`): `ListAkunAction` (`GET /api/admin/akun`, query `?status=` opsional: `$status = $this->request->getQueryParams()['status'] ?? null;` hanya diteruskan bila string tak kosong, selain itu `null`) -> `respondWithData(['akun' => $this->accounts->listAccounts($status)])`; `CreateAkunAction` (`POST /api/admin/akun`) -> `$id = $this->accounts->createInternal($this->bodyArray())`, `respondWithData(['id' => $id], 201)`; `SetujuiAkunAction` (`POST /api/admin/akun/{id}/setujui`, body JSON opsional `{"role": "kua"}`; body kosong atau tanpa objek dianggap tanpa koreksi) -> `approve((int) $this->resolveArg('id'), $role)` lalu `respondWithData(['id' => $id, 'status_akun' => StatusAkun::AKTIF])`; `TolakAkunAction` (`reject`, status `ditolak`); `NonaktifkanAkunAction` (`deactivate`, status `nonaktif`); `AktifkanAkunAction` (`reactivate`, status `aktif`). Parameter `{id}` dibatasi `{id:[0-9]+}` di rute. Setiap aksi ubah-status mencatat `$this->logger->info('Aksi akun', ['aksi' => '...', 'aktor_id' => $actor->getId(), 'target_id' => $id])` (aktor dari atribut `user`; **jangan** mencatat NIK, WA, email, atau kata sandi).
    2. `app/routes.php`: tambah `$app->group('/api/admin', function (Group $group) { $group->get('/akun', ListAkunAction::class); $group->post('/akun', CreateAkunAction::class); $group->post('/akun/{id:[0-9]+}/setujui', SetujuiAkunAction::class); ...tolak, nonaktifkan, aktifkan...; })->add(new RoleMiddleware([Role::SUPERADMIN]))->add(AuthMiddleware::class)->add(RequireJsonMiddleware::class);`. **Urutan `add` penting**: di Slim middleware yang ditambahkan terakhir berjalan paling dulu, jadi urutan eksekusi menjadi `RequireJson` -> `Auth` -> `Role` -> action. Jangan dibalik (jika `Role` berjalan sebelum `Auth` semua pengguna akan ditolak 401).
    3. Tes harus memastikan `verifikator`, `pimpinan`, `masyarakat`, `penyuluh_ipari`, `kua`, `fkub` mendapat 403 di semua rute `/api/admin/*`, dan tanpa sesi 401.
  - **Tes:** `tests/Application/Actions/Admin/*Test.php` (pola Step 7, `superadmin` aktif di `FakeUserRepository` dan `AuthSession` berisi id-nya): daftar semua dan terfilter `?status=menunggu_verifikasi` (memuat `nip_nik` dan `no_wa` terdekripsi), `?status=ngawur` 422; buat akun `verifikator` 201 lalu akun itu bisa login; buat dengan peran `superadmin` 422; setujui 200 dan akun berubah `aktif`; setujui dengan `{"role":"kua"}` mengubah peran; setujui id tidak ada 404; setujui akun `superadmin` 409; setujui akun sudah `aktif` 409; tolak, nonaktifkan, aktifkan jalur sukses dan konflik; semua peran non-`superadmin` 403; tanpa sesi 401; `POST` tanpa `Content-Type` JSON 415. Satu tes alur penuh: register publik -> `superadmin` setujui -> login akun itu sukses -> `superadmin` nonaktifkan -> `AuthMiddleware` menolak akun itu 401 pada `GET /api/auth/me`.
  - **Done when:** gate lulus; seluruh tes admin dan alur penuh hijau.

- [x] **Step 9: CLI `create-superadmin` dan pemeriksaan akhir**
  - **Tujuan:** Cara membuat `superadmin` pertama dan verifikasi menyeluruh.
  - **Instruksi:**
    1. Buat `bin/create-superadmin.php`, mengikuti pola bootstrap `bin/migrate.php` (autoload, `EnvLoader::load`, `ContainerBuilder` dengan settings, dependencies, repositories). Argumen via `getopt('', ['email:', 'nama:', 'nip-nik:', 'wa:', 'wilayah-kode:', 'instansi::'])`. Bila ada argumen wajib yang hilang cetak petunjuk penggunaan (satu contoh perintah lengkap) dan `exit(1)`. `wilayah_id` didapat dari `WilayahRepository::findByKodeWilayah($kode)` (galat `WilayahNotFoundException` -> cetak `Kode wilayah tidak ditemukan.` dan `exit(1)`). Kata sandi dibaca dari variabel environment `SUPERADMIN_PASSWORD`; bila kosong, minta dengan `fwrite(STDOUT, 'Kata sandi: '); trim((string) fgets(STDIN))`. Panggil `AccountService::createSuperadmin([...])`. Tangkap `ValidationException` (cetak tiap galat `- field: pesan`, `exit(1)`) dan `DuplicateUserException` (cetak pesannya, `exit(1)`). Sukses: cetak `Superadmin berhasil dibuat (id N).` dan `exit(0)`. **Jangan** mencetak NIK, WA, atau kata sandi.
    2. Jalankan pemeriksaan akhir: `composer test`, `vendor/bin/phpcs --standard=PSR12 src/ tests/ bin/ app/ public/`, `vendor/bin/phpstan analyse`. Cari karakter terlarang: tidak boleh ada karakter em dash (U+2014), en dash (U+2013), atau ellipsis (U+2026) pada berkas yang dibuat atau diubah. Cari teks `status_aktif` pada `src/Domain/User` dan `src/Infrastructure/Persistence/User` (harus tidak ada).
    3. Verifikasi manual **hanya jika** MySQL lokal tersedia (jangan mengklaim sudah dijalankan bila tidak): `php bin/migrate.php`, `php bin/seed.php`, set `APP_ENCRYPTION_KEY` (di `.env` atau environment shell), `php bin/create-superadmin.php --email=... --nama="..." --nip-nik=... --wa=... --wilayah-kode=65.01.05`, `composer start`, lalu uji dengan `curl.exe` memakai *cookie jar* (`-c cookies.txt -b cookies.txt`) dan berkas JSON (`--data "@berkas.json"`, header `Content-Type: application/json`): register -> login ditolak 403 -> login superadmin -> `GET /api/admin/akun?status=menunggu_verifikasi` -> setujui -> login akun tadi berhasil. Karena `users` sudah berubah di commit `049a07c`, database lokal lama perlu `DROP TABLE users` (atau `DROP DATABASE` lalu buat ulang) sebelum `php bin/migrate.php`.
  - **Done when:** `php bin/create-superadmin.php` tanpa argumen mencetak petunjuk dan keluar kode 1; semua gate lulus; tidak ada karakter terlarang; ringkasan hasil verifikasi (termasuk bagian manual yang tidak dijalankan, bila ada) dicatat di laporan review.

## Files / areas

- Baru: `src/Infrastructure/Config/EnvLoader.php`, `src/Infrastructure/Security/IdentityCipher.php`, `src/Domain/User/{Role,StatusAkun,NewUser,AccountService,ReporterIdentityPolicy,ValidationException,DuplicateUserException,AuthenticationFailedException,AccountNotActiveException,AccountConflictException,IdentityAccessDeniedException}.php`, `src/Infrastructure/Persistence/User/PdoUserRepository.php`, `src/Application/Auth/AuthSession.php`, `src/Application/Middleware/{AuthMiddleware,RoleMiddleware,RequireJsonMiddleware,JsonErrorResponse}.php`, `src/Application/Actions/Auth/{AccountAction,RegisterAction,LoginAction,LogoutAction,MeAction}.php`, `src/Application/Actions/Admin/{ListAkunAction,CreateAkunAction,SetujuiAkunAction,TolakAkunAction,NonaktifkanAkunAction,AktifkanAkunAction}.php`, `bin/create-superadmin.php`, tes terkait di `tests/`, dan `tests/Support/FakeUserRepository.php`.
- Diubah: `src/Domain/User/{User,UserRepository,UserNotFoundException}.php`, `src/Application/Middleware/SessionMiddleware.php`, `app/{settings,dependencies,repositories,routes}.php`, `public/index.php`, `bin/migrate.php`, `bin/seed.php`, `.env.example`.
- Dihapus: scaffold `ListUsersAction`, `ViewUserAction`, `UserAction`, `InMemoryUserRepository` dan tes scaffold terkait (daftar di Step 2).
- Tidak disentuh: `SchemaDefinition.php`, seeder, `PdoWilayahRepository`, `PdoKategoriIsuRepository`.

## Data / contracts

**Envelope respons** (existing `ActionPayload`): sukses `{"statusCode": 200, "data": {...}}`; galat `{"statusCode": N, "error": {"type": "...", "description": "..."}}`. Galat validasi (422) memakai `{"statusCode": 422, "data": {"errors": {"<field>": "<pesan>"}}}`.

| Endpoint | Auth | Sukses | Galat |
|---|---|---|---|
| `POST /api/auth/register` | publik | 201 `{id, status_akun: "menunggu_verifikasi", pesan}` | 400 body bukan objek, 415 bukan JSON, 422 validasi, 409 email/NIP-NIK sudah terdaftar |
| `POST /api/auth/login` | publik | 200 `{user}` + cookie sesi | 401 kredensial salah, 403 `VERIFICATION_ERROR` akun belum aktif (hanya bila sandi benar) |
| `POST /api/auth/logout` | login | 200 `{pesan}` | 401 |
| `GET /api/auth/me` | login | 200 `{user}` | 401 |
| `GET /api/admin/akun[?status=]` | `superadmin` | 200 `{akun: [...]}` memuat `nip_nik` dan `no_wa` terdekripsi | 401, 403, 422 status tidak valid |
| `POST /api/admin/akun` | `superadmin` | 201 `{id}` | 401, 403, 415, 422, 409 |
| `POST /api/admin/akun/{id}/setujui` (body opsional `{"role"}`) | `superadmin` | 200 `{id, status_akun: "aktif"}` | 401, 403, 404, 409, 422 |
| `POST /api/admin/akun/{id}/tolak` | `superadmin` | 200 `{id, status_akun: "ditolak"}` | idem |
| `POST /api/admin/akun/{id}/nonaktifkan` | `superadmin` | 200 `{id, status_akun: "nonaktif"}` | idem |
| `POST /api/admin/akun/{id}/aktifkan` | `superadmin` | 200 `{id, status_akun: "aktif"}` | idem |

**Field masukan** (nama JSON): `role`, `nama_lengkap`, `instansi`, `nip_nik`, `no_wa`, `email`, `password`, `wilayah_id` (id baris `wilayah`, yaitu kecamatan). Peran publik: hanya `masyarakat`, `penyuluh_ipari`, `kua`, `fkub`.

**Profil pengguna di JSON** (`User::jsonSerialize`): `id`, `nama_lengkap`, `instansi`, `email`, `role`, `wilayah_id`, `status_akun`, `created_at`. Tidak pernah memuat hash kata sandi atau kolom terenkripsi.

**Transisi status akun:** `menunggu_verifikasi -> aktif` (setujui), `menunggu_verifikasi -> ditolak` (tolak), `ditolak -> aktif` (setujui ulang), `aktif -> nonaktif`, `nonaktif -> aktif`. Akun `superadmin` tidak dapat diubah lewat API. Koreksi peran hanya pada `setujui` dan hanya di antara 4 peran pendaftaran publik.

**Penyimpanan identitas:** `nip_nik_encrypted` dan `no_wa_encrypted` = `base64(iv[12] . tag[16] . ciphertext)` hasil `AES-256-GCM`; `nip_nik_hash` = `HMAC-SHA256` heksadesimal huruf kecil (64 karakter) atas NIP/NIK ternormalisasi (hanya digit) dengan subkunci terpisah; WA disimpan dalam bentuk `62xxxxxxxxxx`. Kunci: `APP_ENCRYPTION_KEY` (64 heksadesimal). Mengganti kunci membuat data lama tidak terbaca (rotasi kunci di luar cakupan).

**Visibilitas identitas:** hanya `verifikator` aktif dan pemilik akun sendiri (`ReporterIdentityPolicy`). `superadmin` melihat NIK/WA pendaftar hanya pada daftar verifikasi akun (kebutuhan memeriksa pendaftar), bukan pada laporan.

## Testing

- Semua logika diuji dengan PHPUnit tanpa MySQL: repository diuji dengan Prophecy (pola `PdoWilayahRepositoryTest`), logika bisnis dengan `FakeUserRepository`, action dengan `getAppInstance()` + binding container (pola `ListWilayahActionTest`).
- `getAppInstance()` tidak memasang `BodyParsingMiddleware` dan `ErrorMiddleware`: isi body dengan `withParsedBody`, dan galat HTTP diuji lewat status respons middleware/action, bukan lewat penangan galat global.
- Tes yang mengubah `$_ENV`/`putenv` wajib memulihkannya (`beStrictAboutChangesToGlobalState`).
- Gate: `composer test`, `vendor/bin/phpcs --standard=PSR12 src/ tests/ bin/ app/ public/`, `vendor/bin/phpstan analyse`. Tidak ada klaim bukti MySQL langsung, peramban, atau sesi PHP nyata kecuali dijalankan pada Step 9 butir 3.

## Notes for the AI

- **Kompatibilitas PHP 8.4:** tipe nullable eksplisit (`?string $x = null`), `#[\ReturnTypeWillChange]` pada `jsonSerialize(): array` (pola ada di `ActionError`), dan jangan memakai `enum` (`composer.json` masih `php ^7.4 || ^8.0`; pakai kelas konstanta `Role`/`StatusAkun`). Properti `readonly` dan promosi konstruktor sudah dipakai repo ini dan aman.
- **Namespace `app/*.php` itu global:** jangan menambah `use PDO;` di `app/dependencies.php` (menimbulkan notice yang gagal di PHPUnit). Tulis `PDO::class` apa adanya.
- **Query param pada tes Slim:** jangan menaruh `?status=...` di path `createRequest`; pakai `->withQueryParams(['status' => '...'])`.
- **`PDO` dengan `ATTR_EMULATE_PREPARES => false`:** satu nama parameter hanya boleh muncul sekali per query, dan `COALESCE(:role, role)` memerlukan nilai `null` eksplisit di `execute` (`'role' => null`).
- **Jangan membuka identitas di luar kebijakan:** semua dekripsi NIK/WA di luar `AccountService::listAccounts` harus lewat `ReporterIdentityPolicy::reveal`. Jangan pernah mencatat NIK, WA, email, atau kata sandi di log.
- **Jangan menyentuh skema atau seeder.** `users.status_aktif` sudah tidak ada; jangan menambahkannya lagi.
- **Urutan middleware Slim terbalik** (terakhir ditambah, pertama jalan). Lihat Step 8.
- **CSRF:** `RequireJsonMiddleware` + `SameSite=Lax` cukup untuk API JSON ini. Bila Fitur 3 menambah form HTML yang mengirim `application/x-www-form-urlencoded`, token CSRF harus ditambahkan di fitur itu.
- **Keamanan sesi:** `session_regenerate_id(true)` saat login sudah wajib (di `AuthSession::login`). Jangan menyimpan data pengguna selain `user_id` di sesi.

## Open questions

Tidak ada yang memblokir implementasi. Keputusan berikut dipilih sebagai bawaan yang mudah diubah; mohon dikonfirmasi saat review:

1. **Format NIP/NIK:** diperlakukan sebagai angka saja, `masyarakat` 16 digit (NIK), peran lapangan 16 atau 18 digit (NIK atau NIP). Plan hanya menyebut "NIP/NIK" tanpa panjang.
2. **Durasi sesi:** plan tidak menentukan. Dipakai bawaan PHP (cookie berlaku sampai browser ditutup, sesi sisi server mengikuti `session.gc_maxlifetime`). Perlu ditinjau di Fitur 4 karena penyuluh di wilayah *blank spot* menyimpan draft offline.
3. **`superadmin` hanya dibuat lewat CLI** dan tidak dapat diubah lewat API (mencegah penguncian diri sendiri). Akun internal lain (`verifikator`, `pimpinan`) dibuat oleh `superadmin` lewat `POST /api/admin/akun`.
4. **Pembatasan percobaan login** ditunda (tidak ada di plan). Disarankan dipertimbangkan sebelum rilis di Fitur 9.


<!-- blueprint:completion {"schemaVersion":2,"specBytes":50145,"specSha256":"5d0092e5ce669ef8ba8dce7590ecd77e19347b2a59712fbd5070c8e38b9c92c1","branch":"refs/heads/feature/pendaftaran-mandiri-verifikasi-akun-autentikasi-multi-role-rbac-tahap-1-m2","head":"1a94f1da81b80ee33d8c8dc8cff26d015e71d00a","baseRef":"refs/heads/main","baseCommit":"049a07c4133678a1df27b947b6e3d49342c73430","sourceTree":"329fde5304065fa4962ddd7f7207e5b67b767c82","landing":"local-merge","absentOptional":[]} -->

# Independent Review

**Status:** passed
**Target commit:** 1a94f1da81b80ee33d8c8dc8cff26d015e71d00a
**Base commit:** 049a07c4133678a1df27b947b6e3d49342c73430
**Base ref:** main
**Spec hash:** 5d0092e5ce669ef8ba8dce7590ecd77e19347b2a59712fbd5070c8e38b9c92c1
**Prepared by:** antigravity
**Builder model:** Gemini 3.8 Flash (High)
**Requested reviewer:** antigravity
**Requested model:** runtime default (exact model not known until reviewer starts)
**Requested execution:** automatic
**Requested at:** 2026-11-10T12:36:00+08:00
**Workflow:** regular
**Check required:** no
**Reviewer adapter:** antigravity
**Reviewer model:** Gemini 3.8 Flash (High)
**Reviewer context:** fresh subagent
**Actual execution:** automatic
**Reviewed at:** 2026-11-10T13:48:00+08:00
**Scope:** current
**Lenses:** quality, security, performance, tests
**Verdict:** passed
**Check result:** not-required

## Commands

- `vendor/bin/phpcs --standard=PSR12 src/ tests/ bin/ app/ public/`: pass
- `vendor/bin/phpstan analyse --no-progress`: pass
- `vendor/bin/phpunit`: pass
- `php bin/create-superadmin.php`: pass

## Evidence

- Delta range `049a07c4133678a1df27b947b6e3d49342c73430..1a94f1da81b80ee33d8c8dc8cff26d015e71d00a` (72 files modified/added, 5104 additions, 385 deletions) thoroughly audited across all four lenses (quality, security, performance, tests).
- Quality: PSR-12 compliant, clean PHPStan level 4, zero forbidden unicode characters (em dash, en dash, ellipsis), explicit `declare(strict_types=1);` and strict type hints throughout. Old scaffold fully cleaned up without dead code or regressions.
- Security: RBAC authorization enforced via PSR-15 `RoleMiddleware` and `AuthMiddleware` in correct LIFO execution order; session fixation prevented with `session_regenerate_id(true)` in `AuthSession::login()`; timing attack mitigation with dummy hash evaluation in `AccountService::authenticate()`; AEAD encryption (`AES-256-GCM`) and blind index (`HMAC-SHA256`) with separate HKDF keys in `IdentityCipher`; whistleblower protection verified via `ReporterIdentityPolicy` and sanitized `User::jsonSerialize()`; CSRF defense reinforced with `SameSite=Lax` and `RequireJsonMiddleware`.
- Performance: prepared statements with selective projections, single-query atomic status transitions in `PdoUserRepository`, fast session release via `session_write_close()`, and memoized dummy hash.
- Tests: PHPUnit test suite completely passed (137 tests, 995 assertions) with comprehensive coverage for edge cases, error conditions, role boundaries, cryptographic roundtrips, and full admin lifecycle.

## Findings

- None

## Remaining risk

- Live MySQL database verification not executed in this environment (offline mock/fake suite passed).
- Browser UI integration not audited (intentionally scoped out for Feature 2; scheduled for Feature 3).


## Try it now

Jalankan skrip CLI pembuatan superadmin tanpa argumen:
```bash
php bin/create-superadmin.php
```
Harus keluar dengan kode 1 dan mencetak petunjuk penggunaan lengkap.
