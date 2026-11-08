# Portal BERSATU - Project Overview

<!-- blueprint:source-hash 22c3c208d5a86edc193f2b93067510bc3a1fef21ad9187a99555d9675f50ea1d -->

> Portal web responsif berbasis Progressive Web App (PWA, nama merek/ikon: **BERSATU**) untuk *Strategi Penguatan Early Warning System (EWS) Melalui Promosi Kerukunan Umat Beragama Berbasis Digital* pada Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara (Proyek Perubahan PKN Tk. II Angkatan XXX Tahun 2026 - H. Iramsyah Noor, S.Ag., M.A.P.; Mentor: Prof. Dr. Muhammad Ali Ramdhani, S.TP., M.T.; Coach: Dr. Neneng Maria Kiptyah, M.Pd.; Penguji: Drs. Seno Hartono, DESS; lokus piloting awal Kab. Malinau dengan cakupan penuh 5 Kabupaten/Kota dan 55 Kecamatan se-Kaltara).

## Problem

Kalimantan Utara menempati **peringkat pertama (#1) nasional Indeks Kerukunan Umat Beragama (IKUB) tahun 2025**, namun menghadapi kesenjangan antara modal sosial yang kuat dan infrastruktur tata kelola digital EWS tingkat Kanwil yang belum terintegrasi (skor prioritas metode **ASTRID: 30/30**, di atas isu kompetensi digital ASN/Penyuluh 3TP skor 23 dan data rumah ibadah/wakaf spasial skor 21). Pelaporan potensi konflik keagamaan sebelumnya masih manual, parsial, lisan, dan terlokalisasi per kabupaten/kota tanpa basis data provinsi maupun pemetaan spasial, sehingga respons mitigasi lambat (**3-7 hari** pascainsiden/viral). Di wilayah seluas **75.467 km²** (5 Kabupaten/Kota, 55 Kecamatan) yang sebagian pedalamannya hanya terjangkau via sungai/udara perintis ber-*blank spot*, berbatasan langsung dengan Sabah-Sarawak (Malaysia), serta memiliki **64% masyarakat yang belum memahami PBM No. 9 & No. 8 Tahun 2006** (data PKUB), Portal BERSATU mengintegrasikan tiga terobosan (*Nilai Ekoteologi*, *Kelembagaan Agen Kerukunan Digital*, dan *Teknologi EWS-GIS + Media Hub + Chatbot*) untuk deteksi dini *real-time* dan penguatan imunitas sosial.

## Users

Sistem menerapkan **Role-Based Access Control (RBAC)** dengan 5 kelompok pengguna terautentikasi (7 nilai ENUM `users.role`) serta akses publik:

- **Pelapor Khusus Lapangan (`penyuluh_ipari`, `kua`, `fkub`):** Penyuluh Agama (IPARI), Kepala KUA, Pengurus FKUB, dan Tokoh Agama/Lembaga Adat. Mendaftar mandiri (mengisi NIP/NIK, nama, instansi, WA, email, wilayah; aktif setelah disetujui `superadmin`), mengirim laporan "Lapor Dini" ber-GPS otomatis, mengunggah bukti (foto JPEG, PDF, audio MP3), menyimpan draft *offline*, dan memantau riwayat tiket mandiri.
- **Masyarakat Terverifikasi (`masyarakat`):** Warga yang mendaftar mandiri dan diaktifkan oleh `superadmin`. Mengirim aduan indikasi awal (masuk antrean moderasi `verifikator` sebelum tampil di dashboard resmi), serta mengakses Chatbot "Tanya", direktori rumah ibadah, dan hotline FKUB.
- **Verifikator / Operator (`verifikator`):** Petugas Kankemenag Kab/Kota dan Kanwil yang meninjau laporan lapangan dan antrean moderasi masyarakat, **melihat data identitas pelapor** untuk verifikasi faktual, menolak laporan palsu (`ditolak_hoaks`), menetapkan/mengubah status kerawanan (`hijau`, `kuning`, `merah`), dan mencatat `tindak_lanjut`.
- **Pimpinan (`pimpinan`):** Kakanwil Kemenag Kaltara, Kakankemenag Kab/Kota, dan Forkopimda/Bakesbangpol. Memantau dashboard agregat 5 Kab/Kota, peta GIS detail, dan menerima *instant alert* saat laporan diverifikasi berstatus `merah` (tanpa membuka identitas pribadi pelapor).
- **Super Admin (`superadmin`):** Pokja Teknis IT dan Humas/Media yang memverifikasi pendaftaran akun (serta mengoreksi `role` bila perlu), mengelola pengguna, master wilayah/GeoJSON, kategori isu, kurasi Media Hub, `faq_chatbot`, serta data rumah ibadah, agenda, dan kontak FKUB.
- **Pengunjung Publik (Anonim):** Mengakses beranda kondusivitas 5 Kab/Kota dan peta GIS publik tingkat kecamatan berlabel netral, galeri Media Hub, Bank Naskah Khotbah, Chatbot "Tanya", dan Pusat Informasi Kerukunan.

## Usage model

- **Cakupan Wilayah & Piloting:** Melayani **5 Kabupaten/Kota** (Bulungan, Tarakan, Malinau, Nunukan, Tana Tidung) dan **55 Kecamatan** di Kalimantan Utara (75.467 km²), dengan lokus *piloting* awal di **Kabupaten Malinau**.
- **Ketahanan Offline Wilayah 3TP:** PWA (`manifest.json`, Service Worker, `IndexedDB` / `LocalStorage`) menyimpan draft laporan saat tanpa sinyal dan menyinkronkannya dari browser saat kembali online, disertai kompresi foto JPEG di sisi klien.
- **Visibilitas Identitas Pelapor (*Whistleblower Protection*):** Kolom `nip_nik_encrypted` dan `no_wa_encrypted` dienkripsi (`openssl` `AES-256-GCM`) dengan *blind index* `nip_nik_hash` (`HMAC-SHA256`). Hanya `verifikator`, `superadmin`, dan pemilik tiket yang dapat melihat identitas asli; tampilan `pimpinan` dan publik menyamarkan identitas pelapor.
- **Moderasi, Peta Publik & Pemicu *Instant Alert*:**
  - Akun pendaftar mandiri berstatus `menunggu_verifikasi` hingga diaktifkan `superadmin`.
  - Aduan `masyarakat` wajib melewati moderasi `verifikator`. Status `draft`, `terkirim`, dan `ditolak_hoaks` tidak pernah ditampilkan ke publik.
  - Peta publik hanya menampilkan warna agregat kecamatan dan jumlah laporan terverifikasi valid (`diverifikasi`, `proses_mediasi`, `selesai`) dengan label netral (*Kondusif & Harmonis*, *Dalam Fasilitasi Dialog & Pembinaan*, *Dalam Koordinasi Intensif Lintas Sektor*) tanpa titik koordinat presisi maupun kronologi.
  - *Instant alert* otomatis ke grup pimpinan hanya dikirim **setelah** `verifikator` memverifikasi laporan dan menetapkan status `merah`.
- **Non-Requirements MVP:** Dokumen tata kelola non-perangkat lunak (SK Tim, SOP cetak, buku saku cetak, MoU/PKS, Bimtek tatap muka, laporan evaluasi) berada di luar kode aplikasi. Pengiriman konten mandiri oleh kreator generasi muda dan integrasi LLM berbayar ditunda ke pasca-MVP.

## Features

Urutan fitur mengikuti `blueprint/build-plan.md` dan tahapan resmi *Milestones 60 Hari (8 Minggu)*. Fitur utama (*headline*) adalah **Modul EWS "Lapor Dini", Meja Verifikasi & Dashboard Peta Spasial GIS (Fitur 4-6)**:

1. **Skema Basis Data MySQL & Master Data Wilayah Kaltara (Tahap 1 - M1)** - Koneksi PDO MySQL, skrip migrasi DDL 12 tabel sistem, serta seeder data referensi 5 Kabupaten/Kota & 55 Kecamatan se-Kaltara beserta poligon GeoJSON dan 5 kategori isu EWS.
2. **Pendaftaran Mandiri, Verifikasi Akun & Autentikasi Multi-Role RBAC (Tahap 1 - M2)** - Registrasi mandiri (`masyarakat`, `penyuluh_ipari`, `kua`, `fkub`), antrean persetujuan/koreksi peran oleh `superadmin`, login/sesi, middleware RBAC 7 peran, dan layanan enkripsi identitas pelapor (`AES-256-GCM` + HMAC-SHA256).
3. **Fondasi Progressive Web App (PWA) & Beranda Status Kondusivitas Wilayah (Tahap 2 - M3)** - Mesin *server-rendered* PHP template dengan sistem desain `/impeccable`, `manifest.json` (`Portal BERSATU` / `BERSATU`), Service Worker offline, beranda widget kondusivitas 5 Kabupaten/Kota berlabel netral, ticker agenda lintas iman, dan *Quick Action*.
4. **Modul EWS "Lapor Dini", Aduan Masyarakat & Sinkronisasi Draft Offline (Tahap 2 - M4 - Headline)** - Form pelaporan lapangan dan aduan indikasi awal masyarakat dengan tiket otomatis (`EWS-YYYYMMDD-XXXX`), *auto-geotagging* GPS, unggah multi-bukti (kompresi foto JPEG di klien, PDF, audio MP3), antrean draft lokal `IndexedDB` saat *blank spot*, dan pantauan riwayat tiket mandiri.
5. **Meja Kerja Verifikator, Moderasi Aduan & Tindak Lanjut Mediasi (Tahap 2 - M4/M5 - Headline)** - Antarmuka `verifikator` untuk meninjau laporan dan antrean moderasi masyarakat (dengan akses baca identitas pelapor), menolak hoaks (`ditolak_hoaks`), menetapkan/mengubah status kerawanan (`hijau`, `kuning`, `merah`), dan mencatat `tindak_lanjut`.
6. **Dashboard Pemetaan Spasial GIS (Publik & Eksekutif) + Instant Alert (Tahap 2 - M5 - Headline)** - Peta interaktif Kalimantan Utara berbasis Leaflet.js dalam dua mode (Publik: warna agregat kecamatan & jumlah laporan valid berlabel netral; Eksekutif `pimpinan`/`verifikator`: pin koordinat presisi & statistik provinsi), disertai pengiriman *instant alert* pascaverifikasi status `merah` via Webhook Telegram (Cloudflare Worker Relay) dan WhatsApp API.
7. **Moderation & Tolerance Media Hub & Bank Naskah Khotbah (Tahap 2 - M5)** - Galeri publikasi terkurasi untuk video tokoh agama/adat, animasi, minimal 10 infografis moderasi, podcast *"Suara Harmoni Perbatasan"*, dan ruang unduh Bank Naskah Khotbah bertema kerukunan & ekoteologi dengan filter agama, tema (`kategori_media`), dan format berkas.
8. **Chatbot "Tanya" (FAQ PBM 2006) & Pusat Informasi Layanan Kerukunan (Tahap 3 - M6)** - Asisten virtual berbasis pencocokan `faq_chatbot` (PBM No. 9 & No. 8 Tahun 2006, moderasi, ekoteologi, klarifikasi hoaks perbatasan) dengan *fallback* ke WhatsApp hotline FKUB, dilengkapi direktori geospasial rumah ibadah resmi (`rumah_ibadah`), kalender agenda lintas iman (`agenda_kerukunan`), dan daftar kontak sekretariat FKUB se-Kaltara (`kontak_fkub`).
9. **Panel Super Admin, Master Data & Kesiapan Rilis InfinityFree (Tahap 3/4 - M6-M8)** - Antarmuka administrasi `superadmin` untuk verifikasi akun, CRUD master wilayah/GeoJSON, kategori isu, kurasi Media Hub, FAQ Chatbot, serta data rumah ibadah/agenda/kontak FKUB, ditutup dengan konfigurasi produksi untuk deployment PHP 8.4 & MySQL di InfinityFree.

## Data model

> Bentuk 12 tabel MySQL (`InnoDB`, `utf8mb4`) di bawah ini dikunci (*locked*) karena Fitur 1 hingga Fitur 9 bergantung langsung pada nama kolom dan nilai ENUM berikut.

### 1. `wilayah`
- `id` (`INT`, PK, AUTO_INCREMENT)
- `kode_wilayah` (`VARCHAR(10)`, UNIQUE) - kode Kemendagri kecamatan, mis. `65.01.01`
- `nama_kabupaten` (`VARCHAR(100)`) - Bulungan, Tarakan, Malinau, Nunukan, Tana Tidung
- `nama_kecamatan` (`VARCHAR(100)`) - nama kecamatan (55 kecamatan)
- `latitude_pusat` (`DECIMAL(10,8)`) - titik tengah lintang wilayah
- `longitude_pusat` (`DECIMAL(11,8)`) - titik tengah bujur wilayah
- `polygon_geojson` (`LONGTEXT`) - batas poligon GeoJSON untuk *heat map* Leaflet.js
- **Relasi:** `1:N` ke `users`, `laporan_ews`, `rumah_ibadah`, `agenda_kerukunan`, dan `kontak_fkub`

### 2. `users`
- `id` (`BIGINT`, PK, AUTO_INCREMENT)
- `nip_nik_encrypted` (`TEXT`) - NIP/NIK terenkripsi (`AES-256-GCM`)
- `nip_nik_hash` (`CHAR(64)`, UNIQUE) - *blind index* `HMAC-SHA256` untuk cek keunikan & pencarian
- `nama_lengkap` (`VARCHAR(150)`) - nama lengkap pengguna
- `instansi` (`VARCHAR(150)`, NULLABLE) - nama KUA / FKUB / IPARI / Lembaga Adat
- `no_wa_encrypted` (`TEXT`) - nomor WhatsApp terenkripsi (`AES-256-GCM`)
- `email` (`VARCHAR(100)`, UNIQUE) - alamat email login
- `password_hash` (`VARCHAR(255)`) - hash kata sandi
- `role` (`ENUM('superadmin', 'pimpinan', 'verifikator', 'penyuluh_ipari', 'kua', 'fkub', 'masyarakat')`)
- `wilayah_id` (`INT`, FK -> `wilayah.id`)
- `status_akun` (`ENUM('menunggu_verifikasi', 'aktif', 'ditolak', 'nonaktif')`, DEFAULT `'menunggu_verifikasi'`)
- `status_aktif` (`BOOLEAN`, DEFAULT `FALSE`) - bernilai `TRUE` saat `status_akun = 'aktif'`
- `created_at` (`TIMESTAMP`), `updated_at` (`TIMESTAMP`)
- **Relasi:** `N:1` ke `wilayah`; `1:N` ke `laporan_ews` (`pelapor_id`); `1:N` ke `tindak_lanjut` (`petugas_id`)

### 3. `kategori_isu`
- `id` (`INT`, PK, AUTO_INCREMENT)
- `nama_kategori` (`VARCHAR(100)`) - 5 kategori: Pendirian Rumah Ibadah, Gesekan Antarumat/Ormas SARA, Penyiaran Agama/Penistaan, Aliran Keagamaan Baru, Hoaks Transnasional Perbatasan
- `deskripsi` (`TEXT`)
- **Relasi:** `1:N` ke `laporan_ews` (`kategori_id`)

### 4. `laporan_ews`
- `id` (`BIGINT`, PK, AUTO_INCREMENT)
- `nomor_tiket` (`VARCHAR(50)`, UNIQUE) - format `EWS-YYYYMMDD-XXXX`
- `pelapor_id` (`BIGINT`, FK -> `users.id`)
- `wilayah_id` (`INT`, FK -> `wilayah.id`)
- `alamat_detail` (`TEXT`)
- `latitude` (`DECIMAL(10,8)`), `longitude` (`DECIMAL(11,8)`)
- `kategori_id` (`INT`, FK -> `kategori_isu.id`)
- `judul_laporan` (`VARCHAR(255)`)
- `tanggal_kejadian` (`DATE`)
- `estimasi_pihak_terlibat` (`VARCHAR(100)`, NULLABLE)
- `kronologi` (`TEXT`)
- `status_kerawanan` (`ENUM('hijau', 'kuning', 'merah')`, DEFAULT `'hijau'`)
- `status_laporan` (`ENUM('draft', 'terkirim', 'diverifikasi', 'proses_mediasi', 'selesai', 'ditolak_hoaks')`)
- `alert_terkirim_at` (`DATETIME`, NULLABLE) - waktu pengiriman *instant alert* status `merah`
- `created_at` (`TIMESTAMP`), `updated_at` (`TIMESTAMP`)
- **Relasi:** `N:1` ke `users`, `wilayah`, dan `kategori_isu`; `1:N` ke `bukti_laporan` dan `tindak_lanjut`

### 5. `bukti_laporan`
- `id` (`BIGINT`, PK, AUTO_INCREMENT)
- `laporan_id` (`BIGINT`, FK -> `laporan_ews.id`)
- `file_path` (`VARCHAR(255)`)
- `tipe_file` (`VARCHAR(50)`) - `image/jpeg`, `application/pdf`, `audio/mp3`
- `caption` (`VARCHAR(255)`, NULLABLE)
- `created_at` (`TIMESTAMP`)

### 6. `tindak_lanjut`
- `id` (`BIGINT`, PK, AUTO_INCREMENT)
- `laporan_id` (`BIGINT`, FK -> `laporan_ews.id`)
- `petugas_id` (`BIGINT`, FK -> `users.id`) - verifikator/petugas eksekutor
- `catatan_analisis` (`TEXT`)
- `rekomendasi_mitigasi` (`TEXT`)
- `perubahan_status_kerawanan` (`ENUM('hijau', 'kuning', 'merah')`)
- `tanggal_penanganan` (`DATETIME`)

### 7. `kategori_media`
- `id` (`INT`, PK, AUTO_INCREMENT)
- `nama_kategori` (`VARCHAR(100)`) - tema bahasan (Moderasi Beragama, Ekoteologi, Regulasi PBM 2006, Harmoni Perbatasan)
- `slug` (`VARCHAR(100)`, UNIQUE)
- **Relasi:** `1:N` ke `media_hub` (`kategori_id`)

### 8. `media_hub`
- `id` (`INT`, PK, AUTO_INCREMENT)
- `kategori_id` (`INT`, FK -> `kategori_media.id`, NULLABLE)
- `agama` (`ENUM('semua_agama', 'islam', 'kristen', 'katolik', 'hindu', 'buddha', 'khonghucu')`, DEFAULT `'semua_agama'`)
- `judul` (`VARCHAR(255)`)
- `slug` (`VARCHAR(255)`, UNIQUE)
- `tipe_konten` (`ENUM('video', 'animasi', 'infografis', 'podcast', 'naskah_khotbah')`)
- `url_file` (`VARCHAR(255)`), `thumbnail_url` (`VARCHAR(255)`, NULLABLE)
- `deskripsi` (`TEXT`)
- `tokoh_narasumber` (`VARCHAR(150)`)
- `status_tampil` (`BOOLEAN`, DEFAULT `TRUE`)
- `published_at` (`TIMESTAMP`)

### 9. `faq_chatbot`
- `id` (`INT`, PK, AUTO_INCREMENT)
- `pertanyaan_kunci` (`VARCHAR(255)`)
- `kata_kunci` (`VARCHAR(255)`) - kata kunci pencocokan pertanyaan pengguna
- `jawaban` (`TEXT`)
- `kategori` (`ENUM('pbm_2006', 'moderasi', 'ekoteologi', 'perbatasan')`)

### 10. `rumah_ibadah`
- `id` (`INT`, PK, AUTO_INCREMENT)
- `nama_rumah_ibadah` (`VARCHAR(150)`)
- `agama` (`ENUM('islam', 'kristen', 'katolik', 'hindu', 'buddha', 'khonghucu')`)
- `wilayah_id` (`INT`, FK -> `wilayah.id`)
- `alamat` (`TEXT`)
- `latitude` (`DECIMAL(10,8)`, NULLABLE), `longitude` (`DECIMAL(11,8)`, NULLABLE)
- `status_verifikasi` (`BOOLEAN`, DEFAULT `TRUE`)

### 11. `agenda_kerukunan`
- `id` (`INT`, PK, AUTO_INCREMENT)
- `judul_agenda` (`VARCHAR(200)`), `deskripsi` (`TEXT`)
- `wilayah_id` (`INT`, FK -> `wilayah.id`, NULLABLE)
- `lokasi_tempat` (`VARCHAR(200)`)
- `tanggal_mulai` (`DATETIME`), `tanggal_selesai` (`DATETIME`, NULLABLE)
- `status_tampil` (`BOOLEAN`, DEFAULT `TRUE`)

### 12. `kontak_fkub`
- `id` (`INT`, PK, AUTO_INCREMENT)
- `nama_sekretariat` (`VARCHAR(150)`)
- `wilayah_id` (`INT`, FK -> `wilayah.id`, NULLABLE)
- `alamat` (`TEXT`)
- `no_wa` (`VARCHAR(20)`), `email` (`VARCHAR(100)`, NULLABLE)
- `status_aktif` (`BOOLEAN`, DEFAULT `TRUE`)

## Tech stack

- **PHP 8.4 (`^7.4 || ^8.0`) & Slim Framework 4 (`slim/slim`, `slim/psr7`):** Backend HTTP router, PSR-7 message handling, dan PSR-15 middleware stack.
- **PHP-DI 6 & Monolog 2:** Dependency injection container (`app/dependencies.php`, `app/repositories.php`) dan logging (`logs/app.log`).
- **MySQL (`ext-pdo_mysql`):** Basis data relasional penyimpan 12 tabel sistem, koordinat spasial `DECIMAL`, dan poligon GeoJSON `LONGTEXT`. Tes integrasi MySQL dilewati bila server tidak tersedia.
- **Server-Rendered PHP Templates & `/impeccable` Design System:** Render halaman HTML langsung dari PHP tanpa *build bundler* Node.js, dipoles dengan CSS berbasis token melalui skill `/impeccable` (`Persuade`, `Operate`, `Read`, `Experience`).
- **Progressive Web App (PWA) & Penyimpanan Lokal Klien:** `manifest.json`, Service Worker untuk mode *offline*, `IndexedDB` / `LocalStorage` untuk antrean draft laporan "Lapor Dini", dan kompresi JPEG via HTML5 Canvas.
- **Leaflet.js:** Peta spasial GIS interaktif untuk menampilkan poligon GeoJSON 5 Kabupaten/Kota & 55 Kecamatan serta pin laporan.
- **OpenSSL (`AES-256-GCM` & `HMAC-SHA256`):** Enkripsi dan *blind indexing* identitas sensitif pelapor.
- **Webhook Telegram (Cloudflare Worker Relay) & WhatsApp API:** Pengiriman otomatis notifikasi darurat (*instant alert*) via `cURL` saat laporan diverifikasi berstatus `merah`.
- **PHPUnit 9.5, PHPCS (PSR-12) & PHPStan (Level 4):** Pengujian unit (`composer test`), pemeriksaan standar kode (`vendor/bin/phpcs`), dan analisis statis (`vendor/bin/phpstan`).

## Monetization

**Zero cost bagi pengguna (Akses Gratis untuk Semua).** Sesuai strategi *Marketing 4P + 1C*, Portal BERSATU merupakan layanan publik nirlabal pemerintah pada Kanwil Kementerian Agama Provinsi Kalimantan Utara.

## UI/UX

Antarmuka responsif *mobile-first* yang dirancang dengan `/impeccable` dan 2 lapis label warna (`hijau`: Internal *Kondusif* / Publik *Kondusif & Harmonis*; `kuning`: Internal *Potensi Ketegangan / Butuh Mediasi* / Publik *Dalam Fasilitasi Dialog & Pembinaan*; `merah`: Internal *Ancaman Konflik Mendesak* / Publik *Dalam Koordinasi Intensif Lintas Sektor*):

- `/` - Beranda utama (`Persuade`): widget kondusivitas 5 Kabupaten/Kota berlabel netral, ticker agenda lintas iman, *Quick Action*, dan sorotan Media Hub.
- `/auth/login` & `/auth/register` - Masuk dan pendaftaran mandiri (`masyarakat`, `penyuluh_ipari`, `kua`, `fkub`) dengan notifikasi status verifikasi akun.
- `/ews/lapor` - Formulir "Lapor Dini" & aduan (`Operate`): *auto-geotagging* GPS, 5 kategori isu, unggah bukti (JPEG terkompresi, PDF, MP3), draft *offline* `IndexedDB`, dan pantauan tiket mandiri.
- `/ews/verifikasi` - Meja kerja `verifikator` (`Operate`): verifikasi laporan, moderasi aduan masyarakat, lihat identitas pelapor, penolakan hoaks (`ditolak_hoaks`), penetapan status kerawanan, dan input `tindak_lanjut`.
- `/peta-kerukunan` & `/ews/peta-gis` - Peta GIS Leaflet.js: peta publik tingkat kecamatan berlabel netral di `/peta-kerukunan`, serta dashboard GIS eksekutif detail pin koordinat bagi `pimpinan`/`verifikator`/`superadmin` di `/ews/peta-gis`.
- `/media-hub` - Galeri Moderation & Tolerance Media Hub (`Experience` & `Read`): video tokoh agama/adat, animasi, infografis, podcast *"Suara Harmoni Perbatasan"*, dan ruang unduh Bank Naskah Khotbah.
- `/tanya` - Antarmuka Chatbot "Tanya" (`Read`): tanya-jawab berbasis pencocokan `faq_chatbot` (PBM No. 9 & 8 Tahun 2006, moderasi, ekoteologi, klarifikasi hoaks SARA) dengan *fallback* ke WhatsApp hotline FKUB.
- `/informasi-kerukunan` - Pusat informasi (`Read`): direktori geospasial rumah ibadah resmi, kalender agenda lintas iman, dan kontak sekretariat hotline FKUB se-Kaltara.
- `/admin` - Panel `superadmin` (`Operate`): verifikasi pendaftaran akun, manajemen pengguna, master wilayah/GeoJSON, kategori isu, kurasi Media Hub, FAQ Chatbot, dan data Pusat Informasi Kerukunan.

## Deployment

- **Target Hosting:** **InfinityFree** (`htdocs/`) dengan **PHP 8.4.27** dan **MySQL** (`curl`, `pdo_mysql`, `mbstring`, `openssl`, `gd`, `fileinfo` aktif; `upload_max_filesize: 20M`; `post_max_size: 30M`; SSL Let's Encrypt/ZeroSSL).
- **Struktur Routing & Batas File:** Root `.htaccess` mengarahkan request ke `public/index.php`; direktori `logs/` dan `var/cache/` *writable*; setiap file `.php`/`.js`/`.css` < 1 MB dan setiap unggahan < 10 MB.
- **Variabel Environment:** `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS`, `APP_ENV`, `APP_DEBUG`, `APP_ENCRYPTION_KEY`, `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`, `TELEGRAM_API_BASE_URL` (Cloudflare Worker Relay URL untuk melewati blokir DNS `api.telegram.org` di InfinityFree), `WHATSAPP_API_URL`, `WHATSAPP_API_TOKEN`.
