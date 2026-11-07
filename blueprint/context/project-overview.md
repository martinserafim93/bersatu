# Portal BERSATU - Project Overview

<!-- blueprint:source-hash c76932ac7b274173b66befb8d9ab3b8565d26df5cc55645d242a3b1ca788f893 -->

> Portal web responsif berbasis Progressive Web App (PWA) untuk *Strategi Penguatan Early Warning System (EWS) Melalui Promosi Kerukunan Umat Beragama Berbasis Digital* pada Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara (Proyek Perubahan PKN Tk. II Angkatan XXX Tahun 2026 - H. Iramsyah Noor, S.Ag., M.A.P.; Mentor: Prof. Dr. Muhammad Ali Ramdhani, S.TP., M.T.; Coach: Dr. Neneng Maria Kiptyah, M.Pd.; Penguji: Drs. Seno Hartono, DESS; lokus piloting awal Kab. Malinau dengan cakupan penuh 5 Kabupaten/Kota dan 55 Kecamatan se-Kaltara).

## Problem

Kalimantan Utara meraih skor Indeks Kerukunan Umat Beragama (IKUB) tertinggi nasional pada tahun 2025 (**87,48 / Sangat Tinggi**, peringkat 1 nasional), namun menghadapi kesenjangan antara modal sosial yang kuat dan infrastruktur tata kelola digital EWS di tingkat Kanwil yang belum terintegrasi (skor prioritas metode **ASTRID: 30/30**). Pelaporan potensi konflik keagamaan selama ini masih manual, parsial, lisan, dan terfragmentasi per kabupaten/kota tanpa basis data provinsi maupun pemetaan spasial, sehingga respons mitigasi lambat (**3 hingga 7 hari** pascainsiden/viral). Di wilayah seluas **75.467,70 km²** (5 Kabupaten/Kota, 55 Kecamatan, 6 agama, 11 Lembaga Adat, 15 Paguyuban) yang didominasi jalur sungai/udara perintis ber-*blank spot*, berbatasan langsung dengan Sabah-Sarawak (Malaysia), serta memiliki **64% masyarakat yang belum memahami PBM No. 9 & No. 8 Tahun 2006**, Portal BERSATU mengintegrasikan tiga terobosan (*Nilai Ekoteologi*, *Kelembagaan Agen Kerukunan Digital*, dan *Teknologi EWS-GIS + Media Hub + AI Chatbot*) untuk deteksi dini *real-time* dan penguatan imunitas sosial dari hoaks SARA maupun paham ekstremis transnasional.

## Users

Sistem menggunakan **Role-Based Access Control (RBAC)** dengan 5 kelompok aktor utama (7 nilai ENUM `users.role`) serta akses publik:

- **Pelapor Khusus Lapangan (*Field Reporter* - `penyuluh_ipari`, `kua`, `fkub`):** Penyuluh Agama (IPARI), Kepala KUA, Pengurus FKUB, serta Tokoh Agama dan Lembaga Adat. Menginput laporan "Lapor Dini" dengan *auto-geotagging* GPS, mengunggah bukti (foto, PDF, audio), menyimpan draft laporan saat *offline*, dan memantau riwayat tiket mandiri.
- **Masyarakat Terverifikasi (*Verified Citizen* - `masyarakat`):** Warga terverifikasi yang dapat mengirim aduan indikasi awal (masuk ke antrean moderasi verifikator sebelum tampil di dashboard resmi), mengakses Chatbot "Tanya", direktori rumah ibadah, dan layanan darurat hotline FKUB.
- **Verifikator / Operator (`verifikator`):** Petugas Kankemenag Kabupaten/Kota dan Kanwil yang memverifikasi laporan lapangan dan antrean aduan masyarakat, menyaring laporan palsu (`ditolak_hoaks`), menetapkan/mengubah status kerawanan (`hijau`, `kuning`, `merah`), serta mencatat analisis, rekomendasi mitigasi, dan notulen mediasi (`tindak_lanjut`).
- **Pimpinan (*Executive Viewer* - `pimpinan`):** Kakanwil Kemenag Kaltara, Kakankemenag Kabupaten/Kota, dan Forkopimda / Bakesbangpol Kaltara. Memantau dashboard agregat provinsi (5 Kabupaten/Kota), peta spasial GIS sebaran kerawanan, dan menerima *instant alert* saat ada laporan berstatus `merah` (tanpa membuka identitas pelapor yang dilindungi).
- **Super Admin / Pengelola Konten (`superadmin`):** Tim Teknis IT (Prakom) dan Pokja Humas/Media yang mengelola akun pengguna, master data wilayah dan kategori isu, kurasi unggahan Media Hub, serta basis pengetahuan FAQ Chatbot (`faq_chatbot`).
- **Pengunjung Publik (Anonim):** Mengakses beranda status kondusivitas wilayah, galeri Moderation & Tolerance Media Hub, Chatbot "Tanya", serta Pusat Informasi & Layanan Kerukunan.

## Usage model

- **Skala Geografis & Lokus Piloting:** Mencakup **5 Kabupaten/Kota** (Bulungan, Tarakan, Malinau, Nunukan, Tana Tidung) dan **55 Kecamatan** di Kalimantan Utara, dengan lokus *piloting* awal (Minggu ke-8) di **Kabupaten Malinau**.
- **Ketahanan Offline Wilayah 3TP (*Blank Spot*):** Menggunakan arsitektur PWA (`manifest.json`, Service Worker, dan `IndexedDB` / `LocalStorage`) agar pelapor di pedalaman/perbatasan tanpa sinyal dapat menyimpan draft laporan secara lokal dan melakukan sinkronisasi otomatis saat kembali mendapat sinyal, disertai kompresi unggahan foto.
- **Perlindungan Identitas Pelapor (*Whistleblower Protection*):** Identitas pelapor lapangan dienkripsi pada tingkat basis data dan disembunyikan dari tampilan publik maupun tampilan pimpinan umum guna mencegah intimidasi di lapangan.
- **Moderasi Berjenjang & Penyaringan Hoaks:** Pelaporan "Lapor Dini" diprioritaskan bagi pelapor khusus terverifikasi (`penyuluh_ipari`, `kua`, `fkub`), sementara aduan dari jalur `masyarakat` masuk ke antrean moderasi `verifikator` sebelum dipetakan ke dashboard GIS resmi; laporan palsu ditandai `ditolak_hoaks`.
- **Standar 3 Tingkat Kerawanan & Respons Cepat:** `hijau` (Kondusif), `kuning` (Potensi Ketegangan / Butuh Mediasi oleh penyuluh/KUA/FKUB), dan `merah` (Ancaman Konflik Mendesak - otomatis meneruskan *instant alert* via Webhook Telegram/WhatsApp ke grup pimpinan dan tim respons cepat).

## Features

Urutan fitur mengikuti `blueprint/build-plan.md` dan tahapan resmi *Milestones 60 Hari (8 Minggu)*. Fitur utama (*headline*) adalah **Modul EWS "Lapor Dini", Verifikasi Berjenjang & Dashboard Pemetaan Spasial GIS (Fitur 4-6)** yang dipadukan dengan **Moderation & Tolerance Media Hub serta Chatbot "Tanya" (Fitur 7-8)**:

1. **Skema Basis Data & Master Data Wilayah Kaltara (M1-M2)** - Skrip migrasi DDL untuk 8 tabel inti dan tabel pendukung layanan kerukunan, disertai seeder data referensi 5 Kabupaten/Kota & 55 Kecamatan se-Kaltara beserta poligon GeoJSON dan 5 klasifikasi kategori isu EWS.
2. **Autentikasi Multi-Role (RBAC) & Perlindungan Identitas Pelapor (M2)** - Login, manajemen sesi, middleware otorisasi 7 peran (`superadmin`, `pimpinan`, `verifikator`, `penyuluh_ipari`, `kua`, `fkub`, `masyarakat`), manajemen akun pengguna, serta enkripsi dan penyamaran identitas pelapor (*whistleblower protection*).
3. **Fondasi Progressive Web App (PWA) & Beranda Status Kondusivitas Wilayah (M3 & Kelompok Fitur 1)** - Antarmuka responsif mobile dengan `manifest.json` dan Service Worker offline, beranda widget indikator warna kondusivitas 5 Kabupaten/Kota (`Hijau`, `Kuning`, `Merah`), ticker kabar kerukunan & agenda lintas iman, serta *Quick Action* ke "Lapor Dini", "Chatbot Tanya", dan "Hotline FKUB".
4. **Modul EWS "Lapor Dini" & Sinkronisasi Draft Offline (M4 & Kelompok Fitur 2 - Headline)** - Form pelaporan lapangan dengan nomor tiket otomatis (`EWS-YYYYMMDD-XXXX`), *auto-geotagging* koordinat GPS, unggah multi-bukti (kompresi foto JPEG, PDF, audio MP3), penyimpanan draft lokal (`IndexedDB` / `LocalStorage`) untuk wilayah *blank spot* beserta sinkronisasi otomatis, dan pantauan riwayat laporan mandiri.
5. **Verifikasi Berjenjang, Tindak Lanjut Mediasi & Penyaringan Hoaks (M4-M5 & Alur Status EWS)** - Meja kerja verifikator/operator untuk memeriksa laporan lapangan dan antrean moderasi aduan masyarakat, menolak laporan hoaks (`ditolak_hoaks`), menetapkan/mengubah status kerawanan (`hijau`, `kuning`, `merah`), serta mencatat analisis dan rekomendasi mediasi (`tindak_lanjut`).
6. **Dashboard Pemetaan Spasial GIS & Notifikasi Darurat Instan (M5 & Kelompok Fitur 2 - Headline)** - Peta interaktif Kalimantan Utara berbasis Leaflet.js dengan pin koordinat kejadian (`Hijau`, `Kuning`, `Merah`), poligon wilayah rawan (`polygon_geojson` / *heat map*), ringkasan monitoring eksekutif pimpinan, dan *instant alert* otomatis via Webhook Telegram/WhatsApp saat laporan berstatus `merah`.
7. **Moderation & Tolerance Media Hub & Bank Naskah Khotbah (M6 & Kelompok Fitur 4)** - Katalog publikasi terkurasi untuk video testimoni tokoh agama/adat, minimal 10 infografis moderasi, podcast *"Suara Harmoni Perbatasan"*, dan ruang unduh Bank Naskah Khotbah bertema kerukunan & ekoteologi dengan filter agama, tema, dan format berkas serta panel kurasi Super Admin.
8. **Chatbot "Tanya" & Pusat Informasi Layanan Kerukunan (M6 & Kelompok Fitur 3 & 5)** - Asisten virtual berbasis *knowledge base* (`faq_chatbot`) untuk edukasi PBM No. 9 & No. 8 Tahun 2006, moderasi beragama, ekoteologi, dan klarifikasi hoaks SARA perbatasan dengan *fallback* ke hotline FKUB, dilengkapi direktori geospasial rumah ibadah resmi, kalender agenda lintas iman, buku saku digital indikator kerawanan, dan kontak sekretariat FKUB se-Kaltara.

## Data model

> Bentuk 8 tabel inti di bawah ini dikunci (*locked*) karena Fitur 1 hingga Fitur 8 bergantung langsung pada nama kolom dan nilai ENUM berikut.

### 1. `wilayah`
- `id` (`INT`, PK, AUTO_INCREMENT) - identitas wilayah
- `kode_bps` (`VARCHAR(10)`) - kode wilayah BPS kabupaten/kecamatan
- `nama_kabupaten` (`VARCHAR(100)`) - satu dari 5 Kabupaten/Kota Kaltara (Bulungan, Tarakan, Malinau, Nunukan, Tana Tidung)
- `nama_kecamatan` (`VARCHAR(100)`) - nama kecamatan (cakupan 55 kecamatan)
- `latitude_pusat` (`DECIMAL(10,8)`) - titik tengah lintang wilayah
- `longitude_pusat` (`DECIMAL(11,8)`) - titik tengah bujur wilayah
- `polygon_geojson` (`TEXT` / `JSON`) - batas poligon GeoJSON untuk *heat map* Leaflet.js
- **Relasi:** `1:N` ke `users` (`wilayah_id`) dan `1:N` ke `laporan_ews` (`wilayah_id`)

### 2. `users`
- `id` (`BIGINT`, PK, AUTO_INCREMENT) - identitas pengguna
- `nip_nik` (`VARCHAR(30)`, UNIQUE) - NIP ASN/Penyuluh atau NIK warga/tokoh
- `nama_lengkap` (`VARCHAR(150)`) - nama pengguna (dienkripsi/disamarkan saat bertindak sebagai pelapor EWS sesuai *whistleblower protection*)
- `no_wa` (`VARCHAR(20)`) - nomor WhatsApp kontak/notifikasi
- `email` (`VARCHAR(100)`, UNIQUE) - alamat email login
- `password_hash` (`VARCHAR(255)`) - hash kata sandi
- `role` (`ENUM('superadmin', 'pimpinan', 'verifikator', 'penyuluh_ipari', 'kua', 'fkub', 'masyarakat')`) - peran RBAC
- `wilayah_id` (`INT`, FK -> `wilayah.id`) - wilayah penugasan/domisili pengguna
- `status_aktif` (`BOOLEAN`, DEFAULT `TRUE`) - status keaktifan akun
- **Relasi:** `N:1` ke `wilayah`; `1:N` ke `laporan_ews` (`pelapor_id`); `1:N` ke `tindak_lanjut` (`petugas_id`)

### 3. `kategori_isu`
- `id` (`INT`, PK, AUTO_INCREMENT) - identitas kategori isu
- `nama_kategori` (`VARCHAR(100)`) - 5 kategori baku: Pendirian Rumah Ibadah, Gesekan Antarumat/Ormas SARA, Penyiaran Agama/Penistaan, Aliran Keagamaan Baru, Hoaks Transnasional Perbatasan
- `deskripsi` (`TEXT`) - penjelasan indikator kategori isu
- **Relasi:** `1:N` ke `laporan_ews` (`kategori_id`)

### 4. `laporan_ews`
- `id` (`BIGINT`, PK, AUTO_INCREMENT) - identitas laporan EWS
- `nomor_tiket` (`VARCHAR(50)`, UNIQUE) - format baku `EWS-YYYYMMDD-XXXX`
- `pelapor_id` (`BIGINT`, FK -> `users.id`) - referensi pelapor (identitas dilindungi pada layar publik dan pimpinan umum)
- `wilayah_id` (`INT`, FK -> `wilayah.id`) - lokasi kabupaten/kecamatan kejadian
- `alamat_detail` (`TEXT`) - deskripsi lokasi spesifik kejadian
- `latitude` (`DECIMAL(10,8)`) - koordinat lintang GPS kejadian
- `longitude` (`DECIMAL(11,8)`) - koordinat bujur GPS kejadian
- `kategori_id` (`INT`, FK -> `kategori_isu.id`) - klasifikasi isu
- `judul_laporan` (`VARCHAR(255)`) - ringkasan judul insiden/indikasi
- `kronologi` (`TEXT`) - uraian detail kronologi kejadian
- `status_kerawanan` (`ENUM('hijau', 'kuning', 'merah')`) - tingkat risiko kerawanan
- `status_laporan` (`ENUM('draft', 'terkirim', 'diverifikasi', 'proses_mediasi', 'selesai', 'ditolak_hoaks')`) - tahapan siklus hidup laporan
- `created_at` (`TIMESTAMP`) - waktu pembuatan laporan
- `updated_at` (`TIMESTAMP`) - waktu pembaruan terakhir
- **Atribut tambahan spesifikasi form Bagian 3.B:** `tanggal_kejadian` (`DATE`/`DATETIME`) dan `estimasi_pihak_terlibat` (`INT` / `VARCHAR`)
- **Relasi:** `N:1` ke `users`, `wilayah`, dan `kategori_isu`; `1:N` ke `bukti_laporan` dan `tindak_lanjut`

### 5. `bukti_laporan`
- `id` (`BIGINT`, PK, AUTO_INCREMENT) - identitas berkas bukti
- `laporan_id` (`BIGINT`, FK -> `laporan_ews.id`) - laporan terkait
- `file_path` (`VARCHAR(255)`) - path penyimpanan berkas unggahan
- `tipe_file` (`VARCHAR(50)`) - tipe MIME (`image/jpeg`, `application/pdf`, `audio/mp3`)
- `caption` (`VARCHAR(255)`) - keterangan berkas bukti

### 6. `tindak_lanjut`
- `id` (`BIGINT`, PK, AUTO_INCREMENT) - identitas log penanganan/verifikasi
- `laporan_id` (`BIGINT`, FK -> `laporan_ews.id`) - laporan yang ditangani
- `petugas_id` (`BIGINT`, FK -> `users.id`) - verifikator/petugas eksekutor
- `catatan_analisis` (`TEXT`) - hasil verifikasi faktual lapangan / analisis
- `rekomendasi_mitigasi` (`TEXT`) - rekomendasi tindakan atau notulen mediasi
- `perubahan_status_kerawanan` (`ENUM('hijau', 'kuning', 'merah')`) - penetapan/perubahan status risiko
- `tanggal_penanganan` (`DATETIME`) - waktu pencatatan tindak lanjut

### 7. `media_hub`
- `id` (`INT`, PK, AUTO_INCREMENT) - identitas konten moderasi
- `judul` (`VARCHAR(255)`) - judul video, infografis, podcast, atau naskah khotbah
- `slug` (`VARCHAR(255)`, UNIQUE) - pengenal URL ramah baca
- `tipe_konten` (`ENUM('video', 'infografis', 'podcast', 'naskah_khotbah')`) - jenis format konten
- `url_file` (`VARCHAR(255)`) - tautan/berkas media atau dokumen unduhan
- `thumbnail_url` (`VARCHAR(255)`) - gambar pratinjau konten
- `deskripsi` (`TEXT`) - ringkasan isi/materi moderasi atau ekoteologi
- `tokoh_narasumber` (`VARCHAR(150)`) - nama tokoh agama/adat atau penyuluh penulis
- `status_tampil` (`BOOLEAN`, DEFAULT `TRUE`) - status kurasi publikasi
- `published_at` (`TIMESTAMP`) - waktu tayang publikasi
- **Relasi pendukung Bagian 3.D & ERD:** atribut/tabel `kategori_media` untuk penyaringan berdasarkan agama dan tema bahasan (moderasi, ekoteologi, kerukunan perbatasan)

### 8. `faq_chatbot`
- `id` (`INT`, PK, AUTO_INCREMENT) - identitas entri pengetahuan Chatbot "Tanya"
- `pertanyaan_kunci` (`VARCHAR(255)`) - kata kunci/pertanyaan pengguna
- `jawaban` (`TEXT`) - materi jawaban edukasi atau klarifikasi hoaks
- `kategori` (`VARCHAR(100)`) - kelompok bahasan (`pbm_2006`, `moderasi`, `ekoteologi`, `perbatasan`)

### 9. Entitas Layanan Kerukunan (Bagian 3.E)
- Data direktori geospasial rumah ibadah resmi terverifikasi per kabupaten/kota (`wilayah_id`, nama rumah ibadah, agama, alamat, `latitude`, `longitude`), kalender agenda bersama/dialog lintas iman, dan kontak sekretariat hotline FKUB se-Kaltara.

## Tech stack

- **PHP (`^7.4 || ^8.0`) & Slim Framework 4 (`slim/slim`, `slim/psr7`):** Backend HTTP router, PSR-7 message handling, dan PSR-15 middleware stack.
- **PHP-DI 6 & Monolog 2:** Dependency injection container (`app/dependencies.php`, `app/repositories.php`) dan pencatatan log aplikasi (`logs/app.log`).
- **MySQL / PostgreSQL:** Basis data relasional penyimpan 8 tabel inti, data spasial koordinat, dan poligon GeoJSON wilayah.
- **Progressive Web App (PWA) & Penyimpanan Lokal Klien:** `manifest.json`, Service Worker untuk mode *offline*, serta `IndexedDB` / `LocalStorage` untuk antrean draft laporan "Lapor Dini" dan kompresi gambar di sisi klien.
- **Leaflet.js:** Peta spasial GIS interaktif untuk menampilkan poligon GeoJSON 5 Kabupaten/Kota dan pin laporan (`Hijau`, `Kuning`, `Merah`).
- **Webhook Telegram / WhatsApp Bot:** Pengiriman otomatis notifikasi darurat (*instant alert*) saat laporan berstatus `merah`.
- **PHPUnit 9, PHPCS (PSR-12) & PHPStan (Level 4):** Pengujian unit (`composer test`), pemeriksaan standar kode (`vendor/bin/phpcs`), dan analisis statis (`vendor/bin/phpstan`).

## Monetization

**Zero cost bagi pengguna (Akses Gratis untuk Semua).** Sesuai strategi *Marketing 4P + 1C*, Portal BERSATU merupakan layanan publik nirlabal pemerintah pada Kanwil Kementerian Agama Provinsi Kalimantan Utara yang didanai melalui sumber pembiayaan proyek/instansi yang relevan.

## UI/UX

Antarmuka responsif *mobile-first* yang ringan untuk jaringan 3TP, dapat di-*install* di layar utama ponsel (PWA), dan menggunakan sistem kode warna risiko baku di seluruh komponen (`Hijau` = Kondusif, `Kuning` = Potensi Ketegangan/Mediasi, `Merah` = Ancaman Konflik Mendesak):

- `/` - Beranda utama dengan widget status kondusivitas 5 Kabupaten/Kota (Bulungan, Tarakan, Malinau, Nunukan, Tana Tidung), ticker berita kerukunan & agenda lintas iman, serta *Quick Action* ("Lapor Dini", "Chatbot Tanya", "Hotline FKUB").
- `/auth/login` - Halaman autentikasi pengguna multi-role (`nip_nik` / `email`).
- `/ews/lapor` - Formulir "Lapor Dini" dengan *auto-geotagging* GPS, pilihan 5 kategori isu, unggah multi-bukti (foto/PDF/audio), penyimpanan draft *offline*, dan pantauan riwayat tiket pelapor.
- `/ews/verifikasi` - Meja kerja verifikator/operator untuk verifikasi laporan, moderasi aduan masyarakat, penyaringan hoaks (`ditolak_hoaks`), penetapan/perubahan status kerawanan, dan input tindak lanjut mediasi.
- `/ews/peta-gis` - Dashboard eksekutif pimpinan dan peta GIS Leaflet.js se-Kaltara dengan pin kejadian serta *heat map* poligon GeoJSON.
- `/media-hub` - Galeri Moderation & Tolerance Media Hub (video tokoh agama/adat, infografis, podcast *"Suara Harmoni Perbatasan"*) dan ruang unduh Bank Naskah Khotbah dengan filter agama, tema, dan format berkas.
- `/tanya` - Antarmuka Chatbot "Tanya" untuk edukasi PBM No. 9 & 8 Tahun 2006, moderasi, ekoteologi, dan klarifikasi hoaks SARA dengan tombol *fallback* langsung ke hotline FKUB.
- `/informasi-kerukunan` - Pusat informasi berisi direktori geospasial rumah ibadah resmi terverifikasi, kalender agenda dialog lintas iman, buku saku indikator kerawanan, dan daftar kontak hotline FKUB se-Kaltara.
- `/admin` - Panel Super Admin untuk manajemen pengguna, master wilayah/kategori, kurasi konten Media Hub, dan manajemen FAQ Chatbot.

## Deployment

- **Target tahapan rilis (Timeline 60 Hari / 8 Minggu):** Piloting awal di **Kabupaten Malinau** dan peluncuran resmi pada domain Kanwil Kemenag Prov. Kalimantan Utara pada Minggu ke-8 (diikuti replikasi operasional penuh di 5 Kabupaten/Kota dalam jangka menengah 6 bulan).
- **Konfigurasi Server & Runtime:** PHP 7.4/8.x dengan document root diarahkan ke `public/`, direktori `logs/` dan `var/cache/` yang *writable*, serta dukungan kontainer pengembangan melalui `docker-compose.yml` (`composer start` / `php -S localhost:8080 -t public`).
- `> TODO`: Tetapkan pilihan mesin database produksi final (MySQL vs PostgreSQL), variabel environment koneksi DB, kunci enkripsi identitas pelapor, dan kredensial Webhook Bot Telegram/WhatsApp pada Fitur 1, Fitur 2, dan Fitur 6.

## Open questions

1. **Atribut tambahan `laporan_ews` (Bagian 3.B vs Bagian 4):** Spesifikasi form "Lapor Dini" pada Bagian 3.B mencantumkan isian *tanggal kejadian* dan *estimasi jumlah pihak terlibat*, yang belum tertulis eksplisit pada daftar kolom tabel `laporan_ews` di Bagian 4. Keduanya dicatat untuk disertakan sebagai kolom `tanggal_kejadian` dan `estimasi_pihak_terlibat` saat pembuatan migrasi DDL pada Fitur 1.
2. **Kategori agama & tema pada `media_hub` (Bagian 3.D & ERD vs Bagian 4):** ERD Bagian 4 menampilkan entitas `KATEGORI_MEDIA` dan Bagian 3.D mensyaratkan filter berdasarkan *agama*, *tema bahasan*, dan *jenis format berkas*, sedangkan daftar kolom `media_hub` baru memuat `tipe_konten`. Struktur tabel/kolom `kategori_media` (`agama`, `tema`) akan diselesaikan pada skema DDL Fitur 1 dan Fitur 7.
3. **Skema tabel Pusat Informasi & Layanan Kerukunan (Bagian 3.E):** Direktori geospasial tempat ibadah resmi terverifikasi, kalender agenda lintas iman, dan kontak hotline FKUB pada Bagian 3.E belum memiliki rincian kolom khusus di Bagian 4; tabel pendukungnya akan didefinisikan bersama skema DDL pada Fitur 1 dan Fitur 8.
