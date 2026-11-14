# Current Feature: Fondasi Progressive Web App (PWA) & Beranda Status Kondusivitas Wilayah (Tahap 2 - M3)

**Status:** verified
**From build-plan:** feature 3
**Build attempt:** 1
**Branch:** feature/fondasi-progressive-web-app-pwa-beranda-status-kondusivitas-wilayah-tahap-2-m3

## Goal

Membangun fondasi antarmuka *server-rendered* PHP templates dengan sistem desain `/impeccable`, konfigurasi Progressive Web App (`manifest.json` bermerek `Portal BERSATU` / `BERSATU`, Service Worker `sw.js`, halaman fallback `/offline`), halaman beranda (`GET /`) yang menampilkan widget status kondusivitas 5 Kabupaten/Kota se-Kalimantan Utara menggunakan label publik netral, ticker kabar kerukunan & agenda lintas iman, *Quick Action*, serta halaman HTML autentikasi (`GET /auth/login` dan `GET /auth/register`) yang terhubung ke API JSON Fitur 2.

## In scope

- Mesin render template PHP ringan (`App\Application\Views\PhpRenderer`) tanpa ketergantungan *bundler* Node.js, lengkap dengan *layout wrapping*, helper *auto-escaping* XSS (`PhpRenderer::e()`), dan injeksi konteks sesi pengguna.
- Sistem desain CSS berbasis *design tokens* `/impeccable` (`public/assets/css/app.css`) yang mendukung 4 *Surface Modes* (`Persuade`, `Operate`, `Read`, `Experience`), responsif *mobile-first*, dan berukuran jauh di bawah batas 1 MB per file InfinityFree.
- Konfigurasi PWA lengkap:
  - `public/manifest.json` (`name: "Portal BERSATU - Kemenag Kalimantan Utara"`, `short_name: "BERSATU"`, `display: "standalone"`, `start_url: "/"`, `theme_color: "#0b5d46"`).
  - Ikon vektor PWA (`public/assets/icons/icon-192.svg` dan `public/assets/icons/icon-512.svg`).
  - Service Worker (`public/sw.js`) dengan strategi *Network-First* untuk navigasi HTML (jatuh ke `/offline` saat tanpa sinyal di wilayah 3TP) dan *Cache-First / Stale-While-Revalidate* untuk aset statis, tanpa pernah meng-cache endpoint sensitif `/api/auth/*` atau `/api/admin/*`.
  - Skrip klien `public/assets/js/pwa.js` untuk registrasi Service Worker, indikator status koneksi *online/offline*, dan aksi tombol keluar (`POST /api/auth/logout`).
- Aturan domain dan agregasi status kondusivitas publik (`App\Domain\Home\PublicCondusivityStatus`, `App\Domain\Home\HomeSummaryRepository`, `App\Infrastructure\Persistence\Home\PdoHomeSummaryRepository`):
  - Pemetaan dua lapis label warna ke **label publik netral**:
    - `hijau` -> `Kondusif & Harmonis`
    - `kuning` -> `Dalam Fasilitasi Dialog & Pembinaan`
    - `merah` -> `Dalam Koordinasi Intensif Lintas Sektor`
  - Agregasi per Kabupaten/Kota (Malinau sebagai lokus *piloting*, Bulungan, Tarakan, Nunukan, Tana Tidung) yang hanya menghitung laporan terverifikasi valid (`diverifikasi`, `proses_mediasi`, `selesai`) dan tidak pernah mengekspos laporan `draft`, `terkirim`, atau `ditolak_hoaks`, maupun titik koordinat/kronologi.
  - Pengambilan data ticker `agenda_kerukunan` (`status_tampil = 1`) dan sorotan `media_hub` (`status_tampil = 1`) dengan *graceful fallback* apabila database belum terhubung atau tabel masih kosong.
- Endpoint JSON ringkasan publik `GET /api/public/beranda` (`BerandaSummaryApiAction`).
- Halaman HTML Beranda Utama `GET /` (`HomePageAction`), halaman fallback `GET /offline` (`OfflinePageAction`), serta halaman HTML `GET /auth/login` (`LoginPageAction`) dan `GET /auth/register` (`RegisterPageAction`) beserta skrip `public/assets/js/auth.js`.
- Pengujian unit PHPUnit untuk `PhpRenderer`, `PublicCondusivityStatus`, `BerandaSummaryApiAction`, dan render halaman `GET /`, `GET /offline`, `GET /auth/login`, serta `GET /auth/register`.

## Out of scope

- Formulir pelaporan lapangan "Lapor Dini", *auto-geotagging* GPS, unggah bukti, dan penyimpanan draft laporan `IndexedDB` (Fitur 4).
- Meja kerja `verifikator` untuk memverifikasi laporan dan mencatat `tindak_lanjut` (Fitur 5).
- Peta spasial interaktif Leaflet.js tingkat kecamatan dan pengiriman *instant alert* Telegram/WhatsApp (Fitur 6).
- Galeri penuh Media Hub & Bank Naskah Khotbah (Fitur 7), Chatbot "Tanya" & Pusat Informasi Kerukunan (Fitur 8), serta Panel Super Admin HTML `/admin` (Fitur 9).

## Build loop

- **Step reviewMode:** `feature`
- **Checkpoint commits:** `disabled`
- **Logic test gate:** `composer test` (`vendor/bin/phpunit`), ditambah `vendor/bin/phpcs` dan `vendor/bin/phpstan`
- **UI verification:** pemeriksaan respons HTTP & struktur DOM pada halaman server-rendered serta verifikasi nyata di browser saat `/check`

## Build steps

### Step 1 - Mesin Template PHP (`PhpRenderer`), Layout Utama, dan Sistem Desain `/impeccable`

- [x] Buat `src/Application/Views/PhpRenderer.php`:
  - Konstruktor menerima `string $templatesPath` (default ke direktori `templates/` di root proyek) dan `array $globals = []`.
  - Sediakan helper statis `PhpRenderer::e(?string $value): string` yang memanggil `htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`.
  - Sediakan method `render(ResponseInterface $response, string $template, array $data = [], ?string $layout = 'layouts/main.php'): ResponseInterface` yang merender file PHP di dalam `$templatesPath`, membungkus output ke variabel `$content` pada `$layout` (bila `$layout !== null`), menulis HTML ke body `$response`, dan menyetel header `Content-Type: text/html; charset=utf-8`.
  - Lempar `RuntimeException` bila file template atau layout tidak ditemukan.
- [x] Daftarkan `PhpRenderer::class` pada `app/dependencies.php`.
- [x] Buat layout `templates/layouts/main.php` yang memuat:
  - Meta viewport *mobile-first*, `theme-color` (`#0b5d46`), `<link rel="manifest" href="/manifest.json">`, `<link rel="icon" href="/assets/icons/icon-192.svg" type="image/svg+xml">`, dan `<link rel="stylesheet" href="/assets/css/app.css">`.
  - Pita status jaringan *offline/online* (`#network-status-banner`) untuk wilayah 3TP.
  - Header navigasi responsif dengan identitas **Portal BERSATU** (*Kanwil Kemenag Provinsi Kalimantan Utara*), tautan navigasi utama (`Beranda`, `Lapor Dini`, `Peta Kerukunan`, `Media Hub`, `Tanya`, `Info Kerukunan`), serta status sesi pengguna (tombol `Masuk` / `Daftar Agen & Warga` bila belum login, atau badge nama/peran & tombol `Keluar` bila sudah login).
  - Footer institusi dengan keterangan lokus piloting Kabupaten Malinau & cakupan 5 Kabupaten/Kota (55 Kecamatan).
- [x] Buat stylesheet `public/assets/css/app.css` dengan *design tokens* `/impeccable` (palet Hijau Zamrud Kemenag `#0b5d46`, Emas Harmoni Kaltara `#c9962b`, latar hangat `#f7f6f1`, tipografi bersih, badge status kondusivitas `hijau`/`kuning`/`merah`, kartu *Quick Action*, ticker agenda, dan gaya formulir autentikasi).
- [x] Buat unit test `tests/Application/Views/PhpRendererTest.php`.
- **Done when:** `PhpRendererTest` lulus di `composer test`, serta `vendor/bin/phpcs` dan `vendor/bin/phpstan` lulus tanpa error.

### Step 2 - Aset Progressive Web App (`manifest.json`, Service Worker `sw.js`, Ikon SVG, dan Rute `/offline`)

- [x] Buat `public/manifest.json` dengan atribut resmi PWA (`name: "Portal BERSATU - Kemenag Kalimantan Utara"`, `short_name: "BERSATU"`, `start_url: "/"`, `scope: "/"`, `display: "standalone"`, `background_color: "#f7f6f1"`, `theme_color: "#0b5d46"`, dan daftar ikon SVG `192x192` & `512x512`).
- [x] Buat ikon vektor PWA `public/assets/icons/icon-192.svg` dan `public/assets/icons/icon-512.svg`.
- [x] Buat Service Worker `public/sw.js`:
  - Simpan daftar *app shell* pada cache `bersatu-pwa-v1` (`/`, `/offline`, `/manifest.json`, `/assets/css/app.css`, `/assets/js/pwa.js`, `/assets/icons/icon-192.svg`, `/assets/icons/icon-512.svg`).
  - Lewati caching untuk request non-GET serta rute `/api/auth/*` dan `/api/admin/*`.
  - Gunakan *Network-First* dengan fallback `/offline` untuk request navigasi HTML, dan *Stale-While-Revalidate* untuk aset statis.
- [x] Buat `public/assets/js/pwa.js` untuk mendaftarkan `/sw.js`, memperbarui pita indikator *offline/online*, dan menangani klik tombol keluar (`POST /api/auth/logout`).
- [x] Buat template `templates/home/offline.php` dan action `src/Application/Actions/Home/OfflinePageAction.php` (`GET /offline`).
- **Done when:** File `public/manifest.json` valid JSON dengan `short_name: "BERSATU"`, `public/sw.js` dan ikon tersedia di `public/`, serta `GET /offline` merender halaman HTML fallback dengan kode status 200.

### Step 3 - Domain & Repository Agregasi Kondusivitas 5 Kabupaten/Kota, Ticker Agenda, dan API `/api/public/beranda`

- [x] Buat `src/Domain/Home/PublicCondusivityStatus.php`:
  - Konstanta status kerawanan (`HIJAU = 'hijau'`, `KUNING = 'kuning'`, `MERAH = 'merah'`).
  - Konstanta status laporan publik valid (`VALID_PUBLIC_REPORT_STATUSES = ['diverifikasi', 'proses_mediasi', 'selesai']`) dan status laporan risiko aktif (`ACTIVE_RISK_REPORT_STATUSES = ['diverifikasi', 'proses_mediasi']`).
  - Konstanta daftar baku 5 Kabupaten/Kota Kaltara (`DEFAULT_KABUPATEN_LIST`: `Kabupaten Malinau`, `Kabupaten Bulungan`, `Kota Tarakan`, `Kabupaten Nunukan`, `Kabupaten Tana Tidung`, beserta jumlah kecamatan default sehingga total 55 kecamatan).
  - Method `publicLabel(string $statusKerawanan): string` yang memetakan:
    - `'hijau'` -> `'Kondusif & Harmonis'`
    - `'kuning'` -> `'Dalam Fasilitasi Dialog & Pembinaan'`
    - `'merah'` -> `'Dalam Koordinasi Intensif Lintas Sektor'`
  - Method `publicDescription(string $statusKerawanan): string`.
  - Method `resolveKabupatenStatus(int $activeMerahCount, int $activeKuningCount): string` (mengembalikan `'merah'` bila `$activeMerahCount > 0`, `'kuning'` bila `$activeKuningCount > 0`, dan `'hijau'` bila keduanya 0).
  - Method `buildDefaultKabupatenSummary(): array` sebagai *fallback* terstruktur untuk 5 Kabupaten/Kota.
- [x] Buat interface `src/Domain/Home/HomeSummaryRepository.php` dan implementasi `src/Infrastructure/Persistence/Home/PdoHomeSummaryRepository.php`, lalu daftarkan di `app/repositories.php`:
  - `getKabupatenCondusivitySummary(): array` melakukan query agregasi per kabupaten/kota dari `wilayah` dan `laporan_ews` (hanya menghitung laporan berstatus `diverifikasi`, `proses_mediasi`, `selesai`), serta jatuh ke `PublicCondusivityStatus::buildDefaultKabupatenSummary()` bila tabel kosong atau koneksi PDO gagal.
  - `getUpcomingAgendaTicker(int $limit = 5): array` membaca `agenda_kerukunan` (`status_tampil = 1`) dengan fallback daftar agenda lintas iman default.
  - `getMediaHubHighlights(int $limit = 4): array` membaca `media_hub` (`status_tampil = 1`) dengan fallback sorotan konten moderasi & ekoteologi default.
- [x] Buat action `src/Application/Actions/Home/BerandaSummaryApiAction.php` dan daftarkan rute `GET /api/public/beranda` di `app/routes.php`.
- [x] Buat unit test `tests/Domain/Home/PublicCondusivityStatusTest.php` dan `tests/Application/Actions/Home/BerandaSummaryApiActionTest.php`.
- **Done when:** Seluruh skenario pemetaan label publik netral, aturan prioritas risiko `merah` > `kuning` > `hijau`, serta respons JSON `GET /api/public/beranda` lulus di `composer test`, `vendor/bin/phpcs`, dan `vendor/bin/phpstan`.

### Step 4 - Halaman Beranda Utama (`GET /`) dan Halaman Autentikasi (`GET /auth/login`, `GET /auth/register`)

- [x] Buat `src/Application/Actions/Home/HomePageAction.php` dan template `templates/home/index.php`:
  - Menampilkan ticker kabar kerukunan & agenda lintas iman.
  - Menampilkan Hero Portal BERSATU (capaian IKUB #1 Nasional 2025, 5 Kabupaten/Kota & 55 Kecamatan se-Kaltara, lokus piloting Kab. Malinau).
  - Menampilkan Widget Status Kondusivitas 5 Kabupaten/Kota dengan badge warna dan **label publik netral** (*Kondusif & Harmonis*, *Dalam Fasilitasi Dialog & Pembinaan*, *Dalam Koordinasi Intensif Lintas Sektor*), jumlah kecamatan, dan jumlah laporan terverifikasi valid.
  - Menampilkan 5 kartu *Quick Action* (`Lapor Dini EWS`, `Peta Spasial Kerukunan`, `Chatbot "Tanya" PBM 2006`, `Media Hub & Bank Khotbah`, `Pusat Informasi & Hotline FKUB`).
  - Menampilkan cuplikan *Moderation & Tolerance Media Hub* dan pesan *Nilai Ekoteologi*.
- [x] Buat `src/Application/Actions/Auth/LoginPageAction.php` (`GET /auth/login`), `src/Application/Actions/Auth/RegisterPageAction.php` (`GET /auth/register`), template `templates/auth/login.php`, `templates/auth/register.php`, dan skrip `public/assets/js/auth.js`:
  - Formulir `/auth/login` terhubung ke `POST /api/auth/login` dan menampilkan pesan status verifikasi akun secara jelas.
  - Formulir `/auth/register` memuat daftar wilayah kecamatan dari `WilayahRepository` (dengan fallback `GET /api/master/wilayah`), mengizinkan 4 peran pendaftar mandiri (`masyarakat`, `penyuluh_ipari`, `kua`, `fkub`), terhubung ke `POST /api/auth/register`, dan menampilkan informasi bahwa akun baru berstatus `menunggu_verifikasi` hingga disetujui `superadmin`.
- [x] Perbarui `app/routes.php` untuk mendaftarkan `GET /`, `GET /offline`, `GET /auth/login`, dan `GET /auth/register`.
- [x] Buat unit/action test `tests/Application/Actions/Home/HomePageActionTest.php` yang menguji `GET /`, `GET /offline`, `GET /auth/login`, dan `GET /auth/register`.
- **Done when:** `composer test`, `vendor/bin/phpcs`, dan `vendor/bin/phpstan` lulus 100%, serta `GET /` merender halaman HTML lengkap beranda Portal BERSATU.

## Files / areas

- `app/dependencies.php` - registrasi `PhpRenderer` di container PHP-DI.
- `app/repositories.php` - binding `HomeSummaryRepository::class` ke `PdoHomeSummaryRepository::class`.
- `app/routes.php` - rute halaman HTML (`GET /`, `GET /offline`, `GET /auth/login`, `GET /auth/register`) dan endpoint publik (`GET /api/public/beranda`).
- `src/Application/Views/PhpRenderer.php` - mesin render template PHP server-side.
- `src/Domain/Home/PublicCondusivityStatus.php` - aturan domain pemetaan label publik netral dan agregasi status kerawanan 5 Kabupaten/Kota.
- `src/Domain/Home/HomeSummaryRepository.php` - kontrak repository data beranda.
- `src/Infrastructure/Persistence/Home/PdoHomeSummaryRepository.php` - implementasi PDO MySQL dengan fallback aman untuk beranda.
- `src/Application/Actions/Home/HomePageAction.php` - handler `GET /`.
- `src/Application/Actions/Home/OfflinePageAction.php` - handler `GET /offline`.
- `src/Application/Actions/Home/BerandaSummaryApiAction.php` - handler JSON `GET /api/public/beranda`.
- `src/Application/Actions/Auth/LoginPageAction.php` - handler `GET /auth/login`.
- `src/Application/Actions/Auth/RegisterPageAction.php` - handler `GET /auth/register`.
- `templates/layouts/main.php` - layout utama HTML5 & navigasi Portal BERSATU.
- `templates/home/index.php` - template beranda utama (`Persuade`).
- `templates/home/offline.php` - template fallback saat perangkat tanpa sinyal.
- `templates/auth/login.php` - template halaman masuk.
- `templates/auth/register.php` - template halaman pendaftaran mandiri.
- `public/manifest.json` - manifest PWA resmi (`Portal BERSATU` / `BERSATU`).
- `public/sw.js` - Service Worker offline caching.
- `public/assets/css/app.css` - design system CSS `/impeccable`.
- `public/assets/js/pwa.js` - registrasi Service Worker, deteksi koneksi, dan logout.
- `public/assets/js/auth.js` - pengiriman formulir login & registrasi ke `/api/auth/*`.
- `public/assets/icons/icon-192.svg` & `public/assets/icons/icon-512.svg` - ikon PWA.
- `tests/Application/Views/PhpRendererTest.php` - pengujian unit template renderer.
- `tests/Domain/Home/PublicCondusivityStatusTest.php` - pengujian unit label netral & logika agregasi status kabupaten.
- `tests/Application/Actions/Home/BerandaSummaryApiActionTest.php` - pengujian unit API ringkasan beranda.
- `tests/Application/Actions/Home/HomePageActionTest.php` - pengujian render HTML `GET /`, `GET /offline`, `GET /auth/login`, dan `GET /auth/register`.

## Data / contracts

### 1. Aturan Dua Lapis Label Indikator Warna (Publik vs Internal)

| Nilai `status_kerawanan` | Label Internal (`verifikator` / `pimpinan`) | Label Publik Netral (Beranda & Peta Publik) |
| :--- | :--- | :--- |
| `hijau` | `Kondusif` | `Kondusif & Harmonis` |
| `kuning` | `Potensi Ketegangan / Butuh Mediasi` | `Dalam Fasilitasi Dialog & Pembinaan` |
| `merah` | `Ancaman Konflik Mendesak` | `Dalam Koordinasi Intensif Lintas Sektor` |

- **Filter Laporan Publik:** Hanya laporan dengan `status_laporan IN ('diverifikasi', 'proses_mediasi', 'selesai')` yang dihitung sebagai `total_laporan_valid`. Laporan `draft`, `terkirim`, dan `ditolak_hoaks` **wajib diabaikan**.
- **Penentuan Warna Kabupaten/Kota Saat Ini:** Dihitung dari laporan aktif yang sudah diverifikasi (`status_laporan IN ('diverifikasi', 'proses_mediasi')`):
  - Bila jumlah laporan aktif berstatus `merah` > 0 -> status kabupaten adalah `merah`.
  - Bila tidak ada `merah` namun jumlah laporan aktif berstatus `kuning` > 0 -> status kabupaten adalah `kuning`.
  - Selain itu -> status kabupaten adalah `hijau`.

### 2. Kontrak JSON `GET /api/public/beranda`

```json
{
  "statusCode": 200,
  "data": {
    "provinsi": "Kalimantan Utara",
    "peringkat_ikub_nasional": 1,
    "tahun_ikub": 2025,
    "total_kabupaten_kota": 5,
    "total_kecamatan": 55,
    "lokus_piloting": "Kabupaten Malinau",
    "kabupaten_summary": [
      {
        "nama_kabupaten": "Kabupaten Malinau",
        "jumlah_kecamatan": 15,
        "is_piloting": true,
        "status_kerawanan": "hijau",
        "label_publik": "Kondusif & Harmonis",
        "deskripsi_publik": "Situasi kerukunan umat beragama terjaga kondusif dan harmonis melalui sinergi tokoh lintas agama, adat, dan masyarakat.",
        "total_laporan_valid": 0
      }
    ],
    "agenda_ticker": [
      {
        "id": 1,
        "judul_agenda": "Dialog Lintas Iman & Penguatan Ekoteologi Kabupaten Malinau",
        "lokasi_tempat": "Sekretariat FKUB Kabupaten Malinau",
        "nama_kabupaten": "Kabupaten Malinau",
        "tanggal_mulai": "2026-05-20 09:00:00"
      }
    ],
    "media_highlights": [
      {
        "id": 1,
        "judul": "Suara Harmoni Perbatasan: Merawat Bumi, Merawat Kerukunan",
        "tipe_konten": "podcast",
        "agama": "semua_agama",
        "tokoh_narasumber": "Tim Penyuluh Lintas Agama Kanwil Kemenag Kaltara"
      }
    ]
  }
}
```

## Testing

- `tests/Application/Views/PhpRendererTest.php`:
  - Merender template dengan layout utama dan menyuntikkan variabel `$content`.
  - Merender template parsial tanpa layout (`$layout = null`).
  - Melakukan escaping karakter HTML berbahaya (`<script>`) via `PhpRenderer::e()`.
  - Melempar `RuntimeException` bila path template tidak ada.
- `tests/Domain/Home/PublicCondusivityStatusTest.php`:
  - Memastikan `publicLabel('hijau') === 'Kondusif & Harmonis'`, `publicLabel('kuning') === 'Dalam Fasilitasi Dialog & Pembinaan'`, dan `publicLabel('merah') === 'Dalam Koordinasi Intensif Lintas Sektor'`.
  - Memastikan tidak ada kata kunci internal yang menimbulkan kepanikan (`Ancaman Konflik Mendesak`, `Potensi Ketegangan`) pada label publik.
  - Memastikan `resolveKabupatenStatus()` memprioritaskan `merah` di atas `kuning`, dan `kuning` di atas `hijau`.
  - Memastikan `buildDefaultKabupatenSummary()` mengembalikan tepat 5 Kabupaten/Kota dengan total 55 kecamatan dan menandai `Kabupaten Malinau` sebagai `is_piloting: true`.
- `tests/Application/Actions/Home/BerandaSummaryApiActionTest.php`:
  - Menguji `GET /api/public/beranda` dengan mock `HomeSummaryRepository` dan memastikan struktur JSON `ActionPayload` sesuai kontrak.
- `tests/Application/Actions/Home/HomePageActionTest.php`:
  - Menguji `GET /` mengembalikan HTTP 200 HTML yang memuat nama ke-5 Kabupaten/Kota (`Kabupaten Malinau`, `Kabupaten Bulungan`, `Kota Tarakan`, `Kabupaten Nunukan`, `Kabupaten Tana Tidung`), label publik netral, ticker agenda, dan tautan `/manifest.json`.
  - Menguji `GET /offline`, `GET /auth/login`, dan `GET /auth/register` mengembalikan HTTP 200 HTML dengan elemen yang sesuai.

## Panduan Wajib `/ponytail` & `/impeccable` (Untuk Junior Programmer / AI Pelaksana)

Bagian ini mengunci aturan dari skill **`/ponytail`** (*kesederhanaan kode radikal*) dan **`/impeccable`** (*standar kualitas desain antarmuka & larangan anti-AI-slop*) agar implementasi tetap ringkas, bebas *over-engineering*, dan lolos pemeriksaan kualitas visual.

### A. Prinsip `/ponytail` (7 Tangga Kesederhanaan Kode)

Sebelum menulis baris kode apa pun, jalankan tangga keputusan `/ponytail` secara berurutan:
1. **Apakah kode ini perlu ada? (YAGNI):** Jangan membuat kelas *helper*, *ViewFactory*, *ViewModel*, *TemplateCompiler*, *AssetBundler*, atau *EventBus*. Cukup satu kelas `PhpRenderer` (~65 baris) yang menggunakan `ob_start()`, `extract()`, `include`, dan `ob_get_clean()`.
2. **Apakah sudah ada di codebase?** Gunakan kembali kelas yang sudah ada dari Fitur 1 & Fitur 2:
   - `App\Application\Actions\Action` dan `ActionPayload` untuk endpoint JSON `GET /api/public/beranda`.
   - `App\Domain\User\Role::SELF_REGISTERABLE` (`['masyarakat', 'penyuluh_ipari', 'kua', 'fkub']`) untuk validasi daftar pilihan peran di halaman `/auth/register`.
   - `App\Domain\User\StatusAkun` untuk konstanta status akun.
   - `$_SESSION['auth_user_id']`, `$_SESSION['auth_role']`, dan `$_SESSION['auth_nama_lengkap']` yang sudah dikelola oleh `SessionMiddleware`.
3. **Apakah pustaka standar PHP sudah menyediakannya?** Gunakan fungsi bawaan PHP: `htmlspecialchars(..., ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')`, `json_encode()`, `is_file()`.
4. **Apakah fitur bawaan platform browser sudah menyediakannya?**
   - Gunakan atribut validasi HTML5 bawaan (`required`, `type="email"`, `minlength="8"`, `autocomplete`) pada formulir `/auth/login` dan `/auth/register`.
   - Gunakan `fetch()` bawaan browser dengan `credentials: 'same-origin'` tanpa pustaka Axios/jQuery.
   - Gunakan CSS Grid dan Flexbox bawaan tanpa framework CSS eksternal.
5. **Satu baris cukup?** Jika satu ekspresi sederhana sudah jelas, jangan bungkus dalam *method* berlapis.
6. **Lazy tentang solusi, tidak pernah lalai tentang keamanan:** Validasi batas kepercayaan (*trust boundary*), *escaping* XSS (`PhpRenderer::e()`), pemisahan hak akses RBAC (`superadmin` vs `verifikator`), penyembunyian laporan yang belum diverifikasi dari publik, dan aksesibilitas keyboard **wajib tetap utuh**.

### B. Standar Desain & Larangan Mutlak `/impeccable` (`craft-floor.md`)

Seluruh file template (`templates/**/*.php`) dan stylesheet (`public/assets/css/app.css`) **wajib** mematuhi aturan lantai kualitas (*Craft Floor*) dari `.agents/skills/impeccable/reference/craft-floor.md`:

#### 1. Larangan Mutlak (*Absolute Bans* - Jangan Pernah Ditulis di HTML/CSS!)
- **DILARANG membuat *kicker* atau *eyebrow* di atas heading (`<h1>`, `<h2>`, `<h3>`):** Jangan pernah menaruh `<span class="eyebrow">`, `<p class="kicker">`, atau label kecil huruf kapital di atas judul seksi. Biarkan elemen `<h1>` dan `<h2>` berbicara langsung dengan bobot tipografinya sendiri.
- **DILARANG memakai emoji Unicode (`🟢`, `🟡`, `🔴`, `📢`, `🛡️`, `🕌`) sebagai ikon UI:** Seluruh indikator status atau ikon wajib menggunakan titik indikator CSS (`.status-dot`) atau `<svg>` inline geometris bersih (`viewBox="0 0 24 24"`, `fill="none"`, `stroke="currentColor"`, `stroke-width="1.75"`).
- **DILARANG memakai `border-left` atau `border-right` berwarna dengan ketebalan > 1px:** Pada kartu kabupaten, *callout*, atau pesan error/sukses, gunakan `border: 1px solid ...` merata di keempat sisi disertai latar lembut (`background-color`), jangan pernah `border-left: 4px solid ...`.
- **DILARANG menggabungkan `border` dan `box-shadow` lebar pada satu kartu (*Ghost Card*):** Pilih satu sistem elevasi per elemen - gunakan `border: 1px solid var(--border-subtle)` tanpa *drop shadow* lebar, dan jangan pernah membuat *nested cards* (kartu berbingkai di dalam kartu berbingkai).
- **DILARANG memakai *gradient text* (`background-clip: text`), *hard offset shadow* (`box-shadow: 4px 4px 0`), atau penomoran seksi dekoratif (`01 / 02 / 03`).**

#### 2. Spesifikasi Visual, Kontras, & *Browser Surfaces* (`/impeccable` Tokens)
- **Mode Permukaan (*Surface Modes*):**
  - `GET /` menggunakan mode **`Persuade`** (`class="surface-persuade"`): papan maklumat publik & pantauan kondusivitas wilayah Kalimantan Utara yang berwibawa, berakar pada warna zamrud hutan/sungai Kalimantan Utara dan aksen emas tenun perbatasan.
  - `GET /auth/login` dan `GET /auth/register` menggunakan mode **`Operate`** (`class="surface-operate"`): tata letak kerja yang tenang, fokus pada penyelesaian tugas masuk/daftar, label input di atas field, dan pesan status verifikasi akun yang langsung terbaca.
- **Token Warna Wajib di `public/assets/css/app.css` (Kontras WCAG AA >= 4.5:1):**
  - `--bg-canvas: #f6f5ef` (kertas hangat terang, cocok dibaca di bawah cahaya luar ruang lapangan 3TP)
  - `--bg-surface: #ffffff` (permukaan kartu/panel)
  - `--bg-surface-tinted: #eceae0` (permukaan sekunder bertint hangat)
  - `--text-primary: #12241c` (tinta hijau-hitam pekat, kontras > 13:1)
  - `--text-secondary: #334e42` (teks sekunder di-tint dari hue zamrud, **bukan abu-abu netral `#777`**)
  - `--brand-emerald: #0b5d46` (Hijau Zamrud Kemenag)
  - `--brand-emerald-dark: #073f2f`
  - `--brand-gold: #b8841e` (Emas Harmoni Kalimantan Utara)
  - `--border-subtle: #d5d2c4`
  - Indikator Kondusivitas Publik:
    - Hijau (`Kondusif & Harmonis`): `--status-hijau-bg: #e6f4ed`, `--status-hijau-border: #9bd0b5`, `--status-hijau-text: #0b5239`, `--status-hijau-dot: #12805c`
    - Kuning (`Dalam Fasilitasi Dialog & Pembinaan`): `--status-kuning-bg: #fef5e1`, `--status-kuning-border: #e6c373`, `--status-kuning-text: #6e4805`, `--status-kuning-dot: #c48410`
    - Merah (`Dalam Koordinasi Intensif Lintas Sektor`): `--status-merah-bg: #fcebe9`, `--status-merah-border: #e5a49e`, `--status-merah-text: #7a1c14`, `--status-merah-dot: #c9372c`
- **Tipografi & Numerik:**
  - `letter-spacing` judul besar di antara `-0.02em` hingga `-0.03em` (tidak boleh lebih rapat dari `-0.04em`).
  - `text-wrap: balance` pada `h1, h2, h3`, lebar paragraf bacaan `max-width: 68ch`, dan jarak atas heading selalu lebih besar daripada jarak bawahnya (`margin-top: 2rem; margin-bottom: 0.75rem`).
  - `font-variant-numeric: tabular-nums` wajib diterapkan pada semua angka jumlah kecamatan, jumlah laporan terverifikasi, dan tanggal agenda.
- **Kustomisasi *Browser Surfaces* (Wajib ada di `app.css`):**
  - `::selection { background-color: var(--brand-emerald); color: #ffffff; }`
  - `input, textarea, select { caret-color: var(--brand-emerald); }`
  - `:focus-visible { outline: 2px solid var(--brand-emerald); outline-offset: 2px; }`
  - `a { text-underline-offset: 0.2em; }`

## Notes for the AI

- **Kepatuhan PHP 8.4 & PSR-12:** Selalu cantumkan `declare(strict_types=1);` di baris pertama setiap file `.php` (untuk file template HTML di `templates/`, gunakan `<?php declare(strict_types=1); ?>` di baris paling atas), gunakan *explicit nullable types* (`?string $param = null`), dan jaga panjang baris maksimal 120 karakter agar `vendor/bin/phpcs` lulus tanpa peringatan.
- **Jangan Gunakan Karakter Tipografi Terlarang:** Dilarang menggunakan em dash, en dash, atau karakter elipsis tunggal di kode, template HTML, komentar, maupun pesan. Gunakan tanda hubung biasa `-` dan tiga titik `...`.
- **Ketahanan Tanpa Database Aktif:** `PdoHomeSummaryRepository` dan `RegisterPageAction` harus menangkap `\Throwable` saat memanggil `ContainerInterface::get(PDO::class)` atau `WilayahRepository` sehingga aplikasi tetap merender halaman `GET /` dan `GET /auth/register` dengan anggun meskipun server MySQL lokal sedang mati.
- **Pemisahan Peran RBAC:** Jangan menampilkan tautan atau tombol kelola laporan kepada `superadmin` (superadmin hanya mengelola akun dan konten/master data), dan jangan pernah menampilkan identitas pelapor di beranda publik.

## Open questions

- Tidak ada pertanyaan terbuka; seluruh aturan bisnis, label publik netral, daftar 5 Kabupaten/Kota, alur PWA, serta standar `/ponytail` dan `/impeccable` sudah terkunci di dokumen ini.


<!-- blueprint:completion {"schemaVersion":2,"specBytes":27878,"specSha256":"cf55f06bebc897ca40fd19ba88f85b53217345cdcced527bc2bc22253cefdacb","branch":"refs/heads/feature/fondasi-progressive-web-app-pwa-beranda-status-kondusivitas-wilayah-tahap-2-m3","head":"e819a24544e08f7844b96d33c364f3c02a47f012","baseRef":"refs/heads/main","baseCommit":"598f7c3e32eb78480a9b211f01ab6f0621111e37","sourceTree":"f207e8f0e7e4e7c73428cb0dea6e629387d9cc1d","landing":"local-merge","absentOptional":[]} -->

## Findings

### 3/F-01 [P2] closed - Service Worker caches authenticated HTML navigation and logout does not evict cached HTML

**File:** public/sw.js:50
**Found:** 2026-11-14 by /audit (scope: current; lens: quality, security, performance, tests)
**Why it matters:** On `request.mode === 'navigate'`, `public/sw.js:50-53` caches any 200 HTML response except `/admin*`, and `public/assets/js/pwa.js:43-54` redirects to `/` after `POST /api/auth/logout` without clearing cached HTML navigation entries. Because `templates/layouts/main.php:117-126` renders the logged-in user's full name and role badge into `/`, an offline fallback on a shared device after logout can display the previous user's cached header state, and future authenticated HTML routes outside `/admin` (such as `/ews/verifikasi` or `/ews/peta-gis`) would also be cached unless excluded.
**Suggested fix:** Restrict Service Worker navigation caching in `public/sw.js:50` to public pages (exclude `/admin`, `/ews/verifikasi`, `/ews/peta-gis`, `/auth/login`, `/auth/register`) and delete or refresh cached HTML navigation entries in `public/assets/js/pwa.js:52` before redirecting on logout.
**Resolution:** Closed by independent `/audit` at commit `e819a24544e08f7844b96d33c364f3c02a47f012`. Verified `public/sw.js:11-18,54-71` restricts navigation caching and offline cached-HTML fallback to the explicit `PUBLIC_NAV_PATHS` allowlist (`'/', '/offline', '/peta-kerukunan', '/media-hub', '/tanya', '/informasi-kerukunan'`), and `public/assets/js/pwa.js:39-58,73` evicts cached navigation HTML entries from `bersatu-pwa-v1` on logout before redirecting to `/`.

### 3/F-02 [P3] closed - PdoHomeSummaryRepository retries failed PDO resolution three times per beranda request

**File:** src/Infrastructure/Persistence/Home/PdoHomeSummaryRepository.php:179
**Found:** 2026-11-14 by /audit (scope: current; lens: quality, security, performance, tests)
**Why it matters:** When MySQL is unreachable, `$this->container->get(PDO::class)` throws and PHP-DI does not cache the failed resolution. Because `HomePageAction` and `BerandaSummaryApiAction` call `getKabupatenCondusivitySummary()`, `getUpcomingAgendaTicker()`, and `getMediaHubHighlights()` sequentially on the same repository instance, `connection()` attempts three separate PDO connection handshakes on a single request when the database is offline.
**Suggested fix:** Memoize the resolved `?PDO` state on `PdoHomeSummaryRepository` (`private bool $connectionResolved = false; private ?PDO $pdo = null;`) so a failed connection attempt happens at most once per request.
**Resolution:** Closed by independent `/audit` at commit `e819a24544e08f7844b96d33c364f3c02a47f012`. Verified `src/Infrastructure/Persistence/Home/PdoHomeSummaryRepository.php:15-17,183-199` memoizes `$this->connectionResolved` and `$this->pdo`, and `tests/Infrastructure/Persistence/Home/PdoHomeSummaryRepositoryTest.php:18-38` asserts `$container->get(PDO::class)` is called only once across all three summary methods when the database is unavailable.

## Independent review

**Status:** passed
**Target commit:** e819a24544e08f7844b96d33c364f3c02a47f012
**Base commit:** 598f7c3e32eb78480a9b211f01ab6f0621111e37
**Base ref:** main
**Spec hash:** cf55f06bebc897ca40fd19ba88f85b53217345cdcced527bc2bc22253cefdacb
**Prepared by:** antigravity
**Builder model:** unknown (runtime did not expose exact model)
**Requested reviewer:** antigravity
**Requested model:** runtime default (exact model not known until reviewer starts)
**Requested execution:** automatic
**Requested at:** 2026-11-14T23:15:30+08:00
**Workflow:** regular
**Check required:** no
**Reviewer adapter:** antigravity
**Reviewer model:** unknown (runtime did not expose exact model)
**Reviewer context:** fresh subagent
**Actual execution:** automatic
**Reviewed at:** 2026-11-14T23:19:30+08:00
**Scope:** current
**Lenses:** quality, security, performance, tests
**Verdict:** passed
**Check result:** not-required

### Commands

- `vendor/bin/phpcs`: pass
- `vendor/bin/phpstan`: pass
- `composer test`: pass
- `node --check public/sw.js`: pass
- `node --check public/assets/js/pwa.js`: pass
- `node --check public/assets/js/auth.js`: pass

### Evidence

- Reviewed complete `598f7c3e32eb78480a9b211f01ab6f0621111e37..e819a24544e08f7844b96d33c364f3c02a47f012` delta (33 files) against `blueprint/context/current-feature.md` (hash `cf55f06bebc897ca40fd19ba88f85b53217345cdcced527bc2bc22253cefdacb`) and `blueprint/context/coding-standards.md`.
- Verified `PhpRenderer` HTML escaping (`ENT_QUOTES | ENT_SUBSTITUTE`, `UTF-8`) and template path traversal protection (`src/Application/Views/PhpRenderer.php:26-91`).
- Verified `PublicCondusivityStatus` and `PdoHomeSummaryRepository` neutral public label mapping, active-risk resolution (`merah` > `kuning` > `hijau`), and exclusion of unverified reports (`draft`, `terkirim`, `ditolak_hoaks`).
- Verified `F-01` resolution in `public/sw.js:11-18,54-71` (`PUBLIC_NAV_PATHS` allowlist) and `public/assets/js/pwa.js:39-58,73` (`clearCachedNavigationHtml()` on logout), and moved `F-01` to `closed`.
- Verified `F-02` resolution in `src/Infrastructure/Persistence/Home/PdoHomeSummaryRepository.php:15-17,183-199` (`connectionResolved` memoization) and `tests/Infrastructure/Persistence/Home/PdoHomeSummaryRepositoryTest.php:18-38`, and moved `F-02` to `closed`.
- Verified `vendor/bin/phpcs` (109/109 files clean), `vendor/bin/phpstan` (70/70 files, 0 errors), and `composer test` (153 tests, 1095 assertions passing).

### Findings

- None (`F-01` [P2] and `F-02` [P3] verified and moved to `closed`)

### Remaining risk

- Automated browser harness (`Browser tests`) is not configured in `AGENTS.md`; interactive UI flows were verified via PHPUnit PSR-7 DOM/HTTP tests and prior `/check` live session evidence.

## Try it now

Run or do:

```bash
composer start
# In a second terminal:
curl -s http://localhost:8080/api/public/beranda
```

Expected:

HTTP 200 JSON object containing `"statusCode": 200` and `"data"` with `"provinsi": "Kalimantan Utara"`, `"peringkat_ikub_nasional": 1`, `"tahun_ikub": 2025`, `"total_kabupaten_kota": 5`, `"total_kecamatan": 55`, `"lokus_piloting": "Kabupaten Malinau"`, five `kabupaten_summary` items using neutral `label_publik` values (`"Kondusif & Harmonis"` when no active `kuning` or `merah` reports exist), `agenda_ticker`, and `media_highlights`.

This proves:

The Feature 3 public summary pipeline (`HomeSummaryRepository` -> `PublicCondusivityStatus` -> `GET /api/public/beranda`) returns the 5-Kabupaten/Kota status summary with neutral public labels over HTTP.
