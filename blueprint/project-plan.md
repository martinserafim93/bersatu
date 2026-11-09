# Project Plan - Portal BERSATU

> Disusun berdasarkan fakta terverifikasi dari `PROJECT_BRIEF_PORTAL_BERSATU.md`,
> dokumen paparan resmi `PPT PROPER BERSATU FINAL.pdf` (Proyek Perubahan PKN Tk. II
> Angkatan XXX Tahun 2026 - LAN RI & Kementerian Agama RI), dan hasil uji teknis
> lingkungan target hosting (`PHP 8.4.27` + `MySQL` di InfinityFree).
>
> - **Nama Resmi Proyek:** Portal BERSATU (*BERsama Saling jAga Toleransi Umat*)
> - **Nama Merek / Label Ikon PWA (`short_name`):** BERSATU (*Beragama - Bersinergi - Harmoni*)
> - **Judul Aksi Perubahan:** Strategi Penguatan Early Warning System (EWS) Melalui Promosi Kerukunan Umat Beragama Berbasis Digital Pada Kanwil Kementerian Agama Provinsi Kalimantan Utara
> - **Tagline:** *"Merajut Harmoni, Menjaga Kedamaian di Beranda Batas NKRI Melalui Portal EWS"*
> - **Instansi:** Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara / Kantor Kemenag Kabupaten Malinau
> - **Project Leader (Penyusun):** H. Iramsyah Noor, S.Ag., M.A.P. (NDH: 25, NIP: 197205152001121002, Kepala Kantor Kemenag Kab. Malinau)
> - **Sponsor / Mentor:** Prof. Dr. Muhammad Ali Ramdhani, S.TP., M.T. (NIP: 1971106200811009, Kepala BMBPSDM Kemenag RI)
> - **Coach:** Dr. Neneng Maria Kiptyah, M.Pd.
> - **Penguji:** Drs. Seno Hartono, DESS
> - **Lokus Piloting Awal:** Kabupaten Malinau (Cakupan penuh: 5 Kabupaten/Kota dan 55 Kecamatan se-Kalimantan Utara)

## 1. Problem - What problem are we solving?

Kalimantan Utara menempati **peringkat pertama (#1) nasional Indeks Kerukunan Umat Beragama (IKUB) tahun 2025**, namun menghadapi paradoks antara modal sosial yang kuat dan infrastruktur tata kelola digital EWS di tingkat Kanwil yang belum memadai. Berdasarkan analisis isu strategis metode **ASTRID**, isu prioritas utama (skor tertinggi **30/30**, dibandingkan isu ke-2: *Kompetensi digital ASN/Penyuluh 3TP* dengan skor **23**, dan isu ke-3: *Data rumah ibadah & wakaf spasial* dengan skor **21**) adalah: **"Belum terintegrasinya EWS deteksi dini konflik keagamaan dan belum optimalnya peran tokoh lintas agama dalam promosi kerukunan digital berbasis ekoteologi pada Kanwil Kemenag Kaltara."**

Empat kesenjangan utama yang diselesaikan melalui tiga terobosan kepemimpinan adaptif (*Terobosan Nilai Ekoteologi*, *Terobosan Kelembagaan Agen Kerukunan Digital*, dan *Terobosan Teknologi Portal BERSATU*):

1. **Deteksi Dini Masih Manual dan Lambat (Akar Masalah Sistem):** Belum ada aplikasi EWS terpadu di tingkat Kanwil; pelaporan potensi konflik masih manual, parsial, lisan, dan terlokalisasi per kabupaten/kota tanpa basis data provinsi maupun pemetaan spasial, sehingga respons mitigasi lambat (**3 hingga 7 hari** setelah insiden membesar atau viral).
2. **Tantangan Geografis Wilayah 3TP & Risiko Perbatasan Negara:** Wilayah Kalimantan Utara seluas **75.467 km²** mencakup **5 Kabupaten/Kota** (Bulungan, Tarakan, Malinau, Nunukan, Tana Tidung) dan **55 Kecamatan**, yang sebagian pedalamannya hanya terjangkau melalui jalur sungai atau udara perintis dengan keterbatasan sinyal (*blank spot*), serta berbatasan langsung dengan Sabah dan Sarawak (Malaysia) yang rentan terhadap hoaks SARA dan paham transnasional.
3. **Rendahnya Pemahaman Regulasi & Belum Terwadahinya Agen Kerukunan Digital (Akar Masalah SDM):** Data PKUB mencatat **64% masyarakat belum memahami PBM No. 9 dan No. 8 Tahun 2006**, sementara tokoh lintas agama, tokoh adat, penyuluh agama (IPARI), dan pengurus FKUB belum memiliki wadah kerja digital terintegrasi.
4. **Substansi Promosi Kerukunan & Ekoteologi (Akar Masalah Substansi):** Sosialisasi moderasi beragama masih bersifat tatap muka sporadis dan seremonial, sehingga memerlukan bank konten digital kreatif yang memadukan nilai kerukunan dan **ekoteologi** (merawat kerukunan sejalan dengan merawat bumi/alam sebagai amanah bersama).

**Keterkaitan Kebijakan Makro:**
- **PBM Menteri Agama dan Menteri Dalam Negeri No. 9 & No. 8 Tahun 2006.**
- **Asta Cita Poin ke-8:** Menjamin kebebasan beribadah, memperkuat dialog antariman, memitigasi potensi konflik SARA, serta merawat kerukunan aktif berbasis komunitas/institusi.
- **Asta Protas Kementerian Agama RI:** Meningkatkan Kerukunan dan Cinta Kemanusiaan, Digitalisasi Tata Kelola, Layanan Keagamaan Berdampak, Mewujudkan Pendidikan Unggul/Ramah/Terintegrasi serta Pemberdayaan Pesantren, Pemberdayaan Ekonomi Umat, dan Ekoteologi.
- **SDGs 2030:** SDG 16 (Perdamaian, Keadilan, dan Kelembagaan yang Tangguh), SDG 10 (Berkurangnya Kesenjangan di wilayah 3T), SDG 17 (Kemitraan Multipihak / *Pentahelix*), dan SDG 11 (Kota dan Komunitas yang Berkelanjutan).

**Batas Lingkup (Explicit Non-Requirements untuk Kode Aplikasi):**
- Dokumen tata kelola non-perangkat lunak pada paparan proyek perubahan (SK Tim Efektif, naskah cetak SOP Terpadu EWS, buku saku cetak indikator kerawanan, MoU/PKS lintas sektor, penyelenggaraan Bimtek tatap muka, dan dokumen laporan evaluasi piloting) berada di luar lingkup kode sumber aplikasi.
- Fitur pengiriman konten mandiri oleh kreator generasi muda dan integrasi AI berbasis LLM berbayar tidak termasuk dalam MVP awal (dicatat sebagai pengembangan lanjutan pasca-MVP).

## 2. Users - Who is this for?

Sistem menerapkan **Role-Based Access Control (RBAC)** dengan **5 kelompok pengguna terautentikasi** (7 nilai ENUM `users.role`) serta akses **Pengunjung Publik**:

1. **Pelapor Khusus Lapangan (*Field Reporter*)** - role `penyuluh_ipari`, `kua`, `fkub`:
   - **Aktor:** Penyuluh Agama (anggota IPARI), Kepala KUA, Pengurus FKUB, serta Tokoh Agama dan Lembaga Adat.
   - **Alur Akun:** Mendaftar mandiri di portal dengan memilih peran (`penyuluh_ipari`, `kua`, atau `fkub`) dan mengisi NIP/NIK, nama lengkap, instansi/lembaga, nomor WhatsApp, email, serta wilayah tugas. Akun berstatus `menunggu_verifikasi` hingga disetujui oleh `superadmin` (atau dibuatkan langsung oleh `superadmin`).
   - **Hak Akses:** Mengirim laporan pada form "Lapor Dini", penandaan koordinat GPS otomatis, mengunggah bukti dukung (foto JPEG terkompresi, dokumen PDF, audio MP3), menyimpan draft laporan secara lokal saat *offline*, dan memantau riwayat tiket mandiri.
2. **Masyarakat Terverifikasi (*Verified Citizen*)** - role `masyarakat`:
   - **Aktor:** Warga masyarakat di 5 Kabupaten/Kota Kalimantan Utara.
   - **Alur Akun:** Mendaftar mandiri di portal resmi dengan mengisi NIK, nama lengkap, nomor WhatsApp, email, dan wilayah domisili. Akun wajib diverifikasi dan diaktifkan oleh `superadmin` sebelum dapat masuk dan melapor.
   - **Hak Akses:** Mengirim aduan indikasi awal (masuk ke antrean moderasi `verifikator` sebelum dapat dipetakan ke dashboard resmi), mengakses Chatbot "Tanya", direktori rumah ibadah, dan layanan hotline FKUB.
3. **Verifikator / Operator (*Kankemenag Kab/Kota & Kanwil*)** - role `verifikator`:
   - **Aktor:** Petugas operator/analis pada Kankemenag Kabupaten/Kota dan Kanwil Kemenag Kaltara.
   - **Hak Akses:** Meninjau laporan masuk dan antrean moderasi aduan masyarakat, **melihat data identitas pelapor** untuk verifikasi faktual lapangan, menolak laporan palsu (`ditolak_hoaks`), menetapkan/mengubah status tingkat kerawanan (`hijau`, `kuning`, `merah`), serta menginput catatan analisis, rekomendasi mitigasi, dan notulen mediasi (`tindak_lanjut`). Verifikator hanya melihat data pengaju dan mengelola data laporan; tidak mengelola akun pengguna maupun konten.
4. **Pimpinan (*Executive Viewer*)** - role `pimpinan`:
   - **Aktor:** Kakanwil Kemenag Kaltara, Kakankemenag Kabupaten/Kota, dan unsur Forkopimda / Bakesbangpol Kaltara.
   - **Hak Akses:** Memantau dashboard agregat provinsi (5 Kabupaten/Kota), melihat peta spasial GIS sebaran kerawanan secara detail, dan menerima notifikasi darurat instan (*instant alert*) setelah laporan diverifikasi berstatus `merah` (tanpa membuka identitas pribadi pelapor yang dilindungi).
5. **Super Admin / Pengelola Konten** - role `superadmin`:
   - **Aktor:** Pokja Teknis IT (Prakom) dan Pokja Humas/Media Kanwil Kemenag Kaltara.
   - **Hak Akses:** Memverifikasi pendaftaran akun baru (menyetujui, menolak, atau mengoreksi pilihan `role` pendaftar), mengelola seluruh akun pengguna, mengelola master data wilayah (`wilayah`) dan kategori isu (`kategori_isu`), mengelola dan mengkurasi konten Media Hub (`media_hub`, `kategori_media`), mengelola basis data FAQ Chatbot (`faq_chatbot`), serta mengelola direktori rumah ibadah, agenda lintas iman, dan kontak hotline FKUB. Superadmin hanya mengelola akun dan konten/master data, tidak mengakses atau mengelola laporan EWS maupun identitas pelapor pada laporan.
6. **Pengunjung Publik (Tanpa Login):**
   - **Hak Akses:** Melihat beranda status kondusivitas 5 Kabupaten/Kota dan peta GIS publik tingkat kecamatan (dengan label publik yang netral), mengakses galeri Moderation & Tolerance Media Hub serta Bank Naskah Khotbah, berkonsultasi dengan Chatbot "Tanya", dan mengakses Pusat Informasi & Layanan Kerukunan.

## 3. Features - What does the MVP need?

- **Skema Basis Data MySQL & Master Data Wilayah Kaltara:** Migrasi DDL untuk seluruh tabel sistem dan seeder data referensi 5 Kabupaten/Kota & 55 Kecamatan se-Kalimantan Utara beserta poligon GeoJSON dan 5 klasifikasi kategori isu EWS.
- **Pendaftaran Mandiri, Verifikasi Akun & Autentikasi Multi-Role (RBAC):** Alur registrasi mandiri (`masyarakat`, `penyuluh_ipari`, `kua`, `fkub`) dengan status `menunggu_verifikasi`, persetujuan dan koreksi peran oleh `superadmin`, autentikasi sesi pengguna, middleware otorisasi RBAC, serta enkripsi identitas sensitif pelapor (`openssl` `AES-256-GCM`).
- **Fondasi Progressive Web App (PWA) & Beranda Status Kondusivitas Wilayah:** Antarmuka *server-rendered* PHP responsif mobile dengan sistem desain `/impeccable`, `manifest.json` (`name: "Portal BERSATU"`, `short_name: "BERSATU"`), Service Worker offline, widget status kondusivitas 5 Kabupaten/Kota dengan label publik netral, ticker kabar kerukunan & agenda lintas iman, serta *Quick Action*.
- **Modul EWS "Lapor Dini", Aduan Masyarakat & Sinkronisasi Draft Offline:** Formulir pelaporan lapangan dan aduan indikasi awal masyarakat dengan nomor tiket otomatis (`EWS-YYYYMMDD-XXXX`), *auto-geotagging* koordinat GPS, unggah multi-bukti (kompresi foto JPEG di sisi klien, PDF, audio MP3), antrean penyimpanan lokal offline (`IndexedDB` / `LocalStorage`) untuk wilayah *blank spot*, serta pantauan riwayat laporan mandiri.
- **Meja Kerja Verifikator, Moderasi Aduan & Tindak Lanjut Mediasi:** Alur pemeriksaan laporan (`terkirim` -> `diverifikasi` -> `proses_mediasi` / `ditolak_hoaks` -> `selesai`), hak lihat identitas pelapor bagi `verifikator`, penetapan/perubahan tingkat kerawanan (`hijau`, `kuning`, `merah`), dan pencatatan riwayat analisis serta rekomendasi mediasi (`tindak_lanjut`).
- **Dashboard Pemetaan Spasial GIS (Publik & Eksekutif) + Instant Alert Pascaverifikasi:** Peta interaktif Kalimantan Utara berbasis **Leaflet.js** dengan 2 tingkat tampilan (Publik: warna agregat per kecamatan dan jumlah laporan terverifikasi dengan label netral tanpa detail sensitif; Eksekutif `pimpinan`/`verifikator`: pin koordinat kejadian presisi dan detail tiket), disertai pengiriman *instant alert* otomatis setelah laporan diverifikasi berstatus `merah` melalui Webhook Telegram (mendukung Cloudflare Worker Relay) dan/atau WhatsApp API.
- **Moderation & Tolerance Media Hub & Bank Naskah Khotbah:** Katalog publikasi terkurasi berisi video testimoni tokoh agama & adat, animasi, minimal 10 infografis moderasi, arsip podcast *"Suara Harmoni Perbatasan"*, serta ruang unduh **Bank Naskah Khotbah** bertema kerukunan umat dan ekoteologi dengan filter berdasarkan agama, tema bahasan, dan format berkas.
- **Chatbot "Tanya" (Knowledge Base FAQ) & Pusat Informasi Kerukunan:** Asisten virtual berbasis pencocokan kata kunci/pertanyaan pada tabel `faq_chatbot` (PBM No. 9 & No. 8 Tahun 2006, moderasi beragama, ekoteologi, klarifikasi hoaks SARA perbatasan) dengan tombol *fallback* langsung ke WhatsApp hotline FKUB, dilengkapi direktori geospasial rumah ibadah resmi terverifikasi, kalender agenda dialog lintas iman, dan daftar kontak sekretariat FKUB se-Kaltara.
- **Panel Super Admin, Manajemen Master Data & Kesiapan Rilis InfinityFree:** Antarmuka pengelolaan akun pendaftar, master wilayah/GeoJSON, kategori isu, kurasi Media Hub, FAQ Chatbot, direktori rumah ibadah, agenda, dan kontak FKUB, beserta konfigurasi produksi untuk deployment PHP 8.4 & MySQL di InfinityFree.

## 4. Data - What are we storing?

Basis data relasional **MySQL** (`InnoDB`, `utf8mb4`) menyimpan 12 tabel terstruktur:

1. **`wilayah`** (Master 5 Kabupaten/Kota & 55 Kecamatan se-Kaltara):
   - `id` (PK, INT), `kode_wilayah` (VARCHAR 10, UNIQUE - kode Kemendagri, mis. `65.01.01`), `nama_kabupaten` (VARCHAR 100), `nama_kecamatan` (VARCHAR 100), `latitude_pusat` (DECIMAL 10,8), `longitude_pusat` (DECIMAL 11,8), `polygon_geojson` (LONGTEXT).
2. **`users`** (Pengguna multi-role dengan status verifikasi akun & enkripsi pelapor):
   - `id` (PK, BIGINT), `nip_nik_encrypted` (TEXT - AES-256-GCM), `nip_nik_hash` (CHAR 64, UNIQUE - HMAC-SHA256 blind index), `nama_lengkap` (VARCHAR 150), `instansi` (VARCHAR 150, NULLABLE), `no_wa_encrypted` (TEXT - AES-256-GCM), `email` (VARCHAR 100, UNIQUE), `password_hash` (VARCHAR 255), `role` (ENUM: `superadmin`, `pimpinan`, `verifikator`, `penyuluh_ipari`, `kua`, `fkub`, `masyarakat`), `wilayah_id` (FK `wilayah.id`), `status_akun` (ENUM: `menunggu_verifikasi`, `aktif`, `ditolak`, `nonaktif`; satu-satunya penanda status akun, login hanya diizinkan bila `aktif`), `created_at` (TIMESTAMP), `updated_at` (TIMESTAMP).
3. **`kategori_isu`** (5 klasifikasi isu EWS):
   - `id` (PK, INT), `nama_kategori` (VARCHAR 100), `deskripsi` (TEXT).
4. **`laporan_ews`** (Laporan peringatan dini lapangan & aduan masyarakat):
   - `id` (PK, BIGINT), `nomor_tiket` (VARCHAR 50, UNIQUE - `EWS-YYYYMMDD-XXXX`), `pelapor_id` (FK `users.id`), `wilayah_id` (FK `wilayah.id`), `alamat_detail` (TEXT), `latitude` (DECIMAL 10,8), `longitude` (DECIMAL 11,8), `kategori_id` (FK `kategori_isu.id`), `judul_laporan` (VARCHAR 255), `tanggal_kejadian` (DATE), `estimasi_pihak_terlibat` (VARCHAR 100, NULLABLE), `kronologi` (TEXT), `status_kerawanan` (ENUM: `hijau`, `kuning`, `merah`), `status_laporan` (ENUM: `draft`, `terkirim`, `diverifikasi`, `proses_mediasi`, `selesai`, `ditolak_hoaks`), `alert_terkirim_at` (DATETIME, NULLABLE), `created_at` (TIMESTAMP), `updated_at` (TIMESTAMP).
5. **`bukti_laporan`** (Lampiran berkas bukti laporan EWS):
   - `id` (PK, BIGINT), `laporan_id` (FK `laporan_ews.id`), `file_path` (VARCHAR 255), `tipe_file` (VARCHAR 50: `image/jpeg`, `application/pdf`, `audio/mp3`), `caption` (VARCHAR 255, NULLABLE), `created_at` (TIMESTAMP).
6. **`tindak_lanjut`** (Riwayat verifikasi, analisis, mediasi, dan perubahan status risiko):
   - `id` (PK, BIGINT), `laporan_id` (FK `laporan_ews.id`), `petugas_id` (FK `users.id`), `catatan_analisis` (TEXT), `rekomendasi_mitigasi` (TEXT), `perubahan_status_kerawanan` (ENUM: `hijau`, `kuning`, `merah`), `tanggal_penanganan` (DATETIME).
7. **`kategori_media`** (Kategori tema bahasan Media Hub):
   - `id` (PK, INT), `nama_kategori` (VARCHAR 100), `slug` (VARCHAR 100, UNIQUE).
8. **`media_hub`** (Katalog konten moderasi, animasi, infografis, podcast, dan naskah khotbah):
   - `id` (PK, INT), `kategori_id` (FK `kategori_media.id`, NULLABLE), `agama` (ENUM: `semua_agama`, `islam`, `kristen`, `katolik`, `hindu`, `buddha`, `khonghucu`), `judul` (VARCHAR 255), `slug` (VARCHAR 255, UNIQUE), `tipe_konten` (ENUM: `video`, `animasi`, `infografis`, `podcast`, `naskah_khotbah`), `url_file` (VARCHAR 255), `thumbnail_url` (VARCHAR 255, NULLABLE), `deskripsi` (TEXT), `tokoh_narasumber` (VARCHAR 150), `status_tampil` (BOOLEAN, DEFAULT TRUE), `published_at` (TIMESTAMP).
9. **`faq_chatbot`** (Basis pengetahuan Chatbot "Tanya"):
   - `id` (PK, INT), `pertanyaan_kunci` (VARCHAR 255), `kata_kunci` (VARCHAR 255), `jawaban` (TEXT), `kategori` (ENUM: `pbm_2006`, `moderasi`, `ekoteologi`, `perbatasan`).
10. **`rumah_ibadah`** (Direktori geospasial rumah ibadah resmi terverifikasi):
    - `id` (PK, INT), `nama_rumah_ibadah` (VARCHAR 150), `agama` (ENUM: `islam`, `kristen`, `katolik`, `hindu`, `buddha`, `khonghucu`), `wilayah_id` (FK `wilayah.id`), `alamat` (TEXT), `latitude` (DECIMAL 10,8, NULLABLE), `longitude` (DECIMAL 11,8, NULLABLE), `status_verifikasi` (BOOLEAN, DEFAULT TRUE).
11. **`agenda_kerukunan`** (Kalender kegiatan bersama & dialog lintas iman):
    - `id` (PK, INT), `judul_agenda` (VARCHAR 200), `deskripsi` (TEXT), `wilayah_id` (FK `wilayah.id`, NULLABLE), `lokasi_tempat` (VARCHAR 200), `tanggal_mulai` (DATETIME), `tanggal_selesai` (DATETIME, NULLABLE), `status_tampil` (BOOLEAN, DEFAULT TRUE).
12. **`kontak_fkub`** (Sekretariat & hotline FKUB se-Kaltara):
    - `id` (PK, INT), `nama_sekretariat` (VARCHAR 150), `wilayah_id` (FK `wilayah.id`, NULLABLE), `alamat` (TEXT), `no_wa` (VARCHAR 20), `email` (VARCHAR 100, NULLABLE), `status_aktif` (BOOLEAN, DEFAULT TRUE).

## 5. Tech - What stack are we using?

- **Backend Runtime & Framework:** **PHP 8.4** (`^7.4 || ^8.0` di `composer.json`, terverifikasi `PHP 8.4.27` di server target) dengan **Slim Framework 4** (`slim/slim`, `slim/psr7`), **PHP-DI 6**, dan **Monolog 2**.
- **Database & Persistence:** **MySQL** melalui **`PDO` (`ext-pdo_mysql`)** native dengan pola Repository (`src/Domain/` -> `src/Infrastructure/Persistence/`). Pengujian logika murni berjalan tanpa database; pengujian integrasi MySQL dilewati bila server MySQL tidak tersedia.
- **View & Frontend Rendering:** **Server-Rendered PHP Templates** (tanpa ketergantungan build Node.js) dipadukan dengan **CSS Modern Berbasis Design Tokens** yang dirancang menggunakan skill **`/impeccable`** (mode `Persuade`, `Operate`, `Read`, dan `Experience`) serta endpoint JSON Slim 4 untuk interaksi dinamis.
- **Progressive Web App (PWA) & Offline Storage:** `manifest.json`, Service Worker untuk caching aset/halaman offline, `IndexedDB` / `LocalStorage` untuk antrean draft laporan "Lapor Dini" di wilayah *blank spot*, serta kompresi foto JPEG di sisi klien (HTML5 Canvas) sebelum unggah.
- **Pemetaan Spasial (GIS):** **Leaflet.js** untuk visualisasi peta interaktif Kalimantan Utara (layer `polygon_geojson` 5 Kabupaten/Kota & 55 Kecamatan serta pin kejadian).
- **Enkripsi & Keamanan:** Ekstensi **`openssl`** (`AES-256-GCM` + `hash_hmac` SHA-256) untuk enkripsi identitas pelapor, `password_hash()` (Bcrypt/Argon2) untuk kredensial akun, dan `fileinfo` + `gd` untuk validasi berkas unggahan.
- **Integrasi Notifikasi Darurat (*Instant Alert*):** Klien HTTP `cURL` dengan *configurable endpoint* (`TELEGRAM_API_BASE_URL` melalui **Cloudflare Worker Relay** agar tidak terhalang blokir DNS `api.telegram.org` di InfinityFree) dan dukungan **WhatsApp Cloud API (`graph.facebook.com`)** / gateway WhatsApp.
- **Quality & Testing Tools:** **PHPUnit 9.5** (`composer test`), **PHP_CodeSniffer** PSR-12 (`vendor/bin/phpcs`), dan **PHPStan** level 4 (`vendor/bin/phpstan`).

## 6. Monetize - How will this make money?

**Zero cost bagi pengguna (Akses Gratis untuk Semua).** Berdasarkan strategi *Marketing 4P + 1C* (Slide 12 paparan resmi), Portal BERSATU adalah layanan publik pemerintah nirlabal pada Kanwil Kementerian Agama Provinsi Kalimantan Utara. Pengembangan dan operasional didukung oleh sumber pembiayaan proyek/instansi yang relevan.

## 7. UI/UX - How should this look and feel?

- **Pendekatan Desain (`/impeccable`):** Menggunakan 4 mode permukaan (*Surface Modes*) dari `/impeccable`:
  - **`Persuade`:** Beranda utama (`/`) untuk menampilkan wibawa institusi, capaian IKUB #1 Nasional, dan menggerakkan partisipasi.
  - **`Operate`:** Formulir kerja "Lapor Dini" (`/ews/lapor`), Meja Kerja Verifikator (`/ews/verifikasi`), Dashboard Peta GIS Eksekutif (`/ews/peta-gis`), dan Panel Super Admin (`/admin`).
  - **`Read`:** Chatbot "Tanya" (`/tanya`), Bank Naskah Khotbah, dan Pusat Informasi Kerukunan (`/informasi-kerukunan`).
  - **`Experience`:** Galeri visual dan audio pada Moderation & Tolerance Media Hub (`/media-hub`).
- **Dua Lapisan Label Indikator Warna (Menjaga Ketertiban & Mencegah Kepanikan Publik):**
  - **`hijau`:** Internal: *Hijau - Kondusif* | Publik: *Hijau - Kondusif & Harmonis*.
  - **`kuning`:** Internal: *Kuning - Potensi Ketegangan / Butuh Mediasi* | Publik: *Kuning - Dalam Fasilitasi Dialog & Pembinaan*.
  - **`merah`:** Internal: *Merah - Ancaman Konflik Mendesak* | Publik: *Merah - Dalam Koordinasi Intensif Lintas Sektor*.
- **Rute dan Layar Utama:**
  - `/` - Beranda utama (status kondusivitas 5 Kab/Kota, ticker agenda lintas iman, *Quick Action*, cuplikan Media Hub).
  - `/auth/login` & `/auth/register` - Halaman masuk dan pendaftaran mandiri (`masyarakat`, `penyuluh_ipari`, `kua`, `fkub`) beserta informasi status verifikasi akun.
  - `/ews/lapor` - Formulir "Lapor Dini" & aduan masyarakat (*auto-geotagging* GPS, 5 kategori isu, unggah bukti foto/PDF/audio, draft offline `IndexedDB`, dan daftar riwayat tiket mandiri).
  - `/ews/verifikasi` - Meja kerja `verifikator` (antrean laporan lapangan & moderasi aduan masyarakat, akses lihat identitas pelapor, penetapan status kerawanan, penolakan hoaks, dan input `tindak_lanjut`).
  - `/peta-kerukunan` & `/ews/peta-gis` - Peta GIS Kalimantan Utara berbasis Leaflet.js (mode publik tingkat kecamatan dengan label netral di `/peta-kerukunan`, serta mode eksekutif detail pin koordinat bagi `pimpinan`/`verifikator` di `/ews/peta-gis`).
  - `/media-hub` - Galeri Moderation & Tolerance Media Hub (video tokoh agama/adat, animasi, infografis, podcast *"Suara Harmoni Perbatasan"*) dan ruang unduh Bank Naskah Khotbah dengan filter agama, tema, dan format.
  - `/tanya` - Antarmuka Chatbot "Tanya" berbasis pencocokan FAQ (`pbm_2006`, `moderasi`, `ekoteologi`, `perbatasan`) dengan tombol *fallback* ke WhatsApp hotline FKUB.
  - `/informasi-kerukunan` - Direktori geospasial rumah ibadah resmi terverifikasi, kalender agenda lintas iman, dan daftar kontak sekretariat hotline FKUB se-Kaltara.
  - `/admin` - Panel `superadmin` untuk verifikasi pendaftar akun baru, manajemen pengguna, master wilayah/GeoJSON, kategori isu, kurasi Media Hub, FAQ Chatbot, dan data Pusat Informasi Kerukunan.

## 8. Deployment - Where and how will this ship?

- **Target Hosting Produksi:** **InfinityFree** (`htdocs/`) dengan **PHP 8.4.27** dan **MySQL** (telah diverifikasi mendukung `curl`, `pdo_mysql`, `mbstring`, `openssl`, `gd`, `fileinfo`, `upload_max_filesize: 20M`, `post_max_size: 30M`, dan SSL gratis Let's Encrypt/ZeroSSL).
- **Routing & File Structure:** File `.htaccess` di root mengarahkan trafik ke `public/index.php`. Semua file `.php`, `.js`, dan `.css` dijaga di bawah batas 1 MB per file, dan unggahan bukti di bawah 10 MB per file.
- **Konfigurasi Environment (`.env` / `app/settings.php`):**
  - `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASS` (koneksi MySQL).
  - `APP_ENV`, `APP_DEBUG`, `APP_ENCRYPTION_KEY` (kunci 32-byte untuk `AES-256-GCM` identitas pelapor & HMAC blind index).
  - `TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`, `TELEGRAM_API_BASE_URL` (diarahkan ke URL Cloudflare Worker Relay di produksi karena DNS langsung `api.telegram.org` diblokir oleh InfinityFree).
  - `WHATSAPP_API_URL`, `WHATSAPP_API_TOKEN` (opsional untuk pengiriman melalui `graph.facebook.com` atau gateway WA).
- **Pentahapan Rilis (Sesuai Slide 7 Paparan Resmi - 60 Hari / 8 Minggu):**
  - **Tahap 1 (Minggu 1-2):** Persiapan, skema database MySQL, master wilayah 5 Kab/Kota & 55 Kecamatan, dan autentikasi RBAC.
  - **Tahap 2 (Minggu 3-5):** Pengembangan prototipe Portal BERSATU (PWA), modul EWS "Lapor Dini", Verifikasi, Peta GIS, *Instant Alert*, dan Media Hub.
  - **Tahap 3 (Minggu 6-7):** Penyelesaian modul Chatbot "Tanya", Pusat Informasi Kerukunan, Panel Admin, serta pelaksanaan Bimtek bagi Penyuluh Agama (IPARI) dan kerjasama FKUB.
  - **Tahap 4 (Minggu 8):** Uji coba lapangan (*piloting*) di **Kabupaten Malinau**, peluncuran resmi pada hosting produksi, dan evaluasi.

## 9. Usage model and constraints (optional)

- **Cakupan Wilayah:** 5 Kabupaten/Kota dan 55 Kecamatan di Provinsi Kalimantan Utara (luas 75.467 km²), dengan lokus piloting awal di Kabupaten Malinau.
- **Ketahanan Offline Wilayah 3TP:** Penyimpanan draft laporan di perangkat (`IndexedDB` / `LocalStorage`) melalui PWA saat tanpa sinyal dan sinkronisasi dari browser pengguna saat kembali online (menggunakan sesi browser yang sama sehingga lolos dari *anti-bot cookie check* InfinityFree).
- **Aturan Visibilitas Identitas Pelapor (*Whistleblower Protection*):** Identitas pelapor dienkripsi di database (`AES-256-GCM`). Hanya `verifikator` dan pelapor pemilik tiket yang dapat melihat identitas asli (`superadmin` hanya mengelola akun, bukan laporan); tampilan `pimpinan` dan publik menyamarkan identitas pelapor.
- **Aturan Moderasi & Pemicu *Instant Alert*:** Akun pendaftar mandiri baru aktif setelah disetujui `superadmin`. Laporan dari `masyarakat` masuk antrean moderasi `verifikator`. *Instant alert* ke grup pimpinan hanya dikirim setelah `verifikator` memverifikasi laporan dan menetapkan status `merah`.
- **Aturan Peta & Status Publik:** Publik hanya melihat status agregat kabupaten/kecamatan dan jumlah laporan yang telah diverifikasi valid (`diverifikasi`, `proses_mediasi`, `selesai`) dengan label netral; laporan berstatus `draft`, `terkirim`, atau `ditolak_hoaks` tidak pernah ditampilkan ke publik.
