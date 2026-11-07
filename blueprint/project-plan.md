# Project Plan - Portal BERSATU

> Disusun berdasarkan `PROJECT_BRIEF_PORTAL_BERSATU.md` dan dokumen paparan resmi
> `PPT PROPER BERSATU FINAL.pdf` (Proyek Perubahan Pelatihan Kepemimpinan Nasional
> Tingkat II Angkatan XXX Tahun 2026 - LAN RI & Kementerian Agama RI).
>
> - **Nama Proyek:** Portal BERSATU (*BERsama Saling jAga Toleransi Umat*)
> - **Judul Aksi Perubahan:** Strategi Penguatan Early Warning System (EWS) Melalui Promosi Kerukunan Umat Beragama Berbasis Digital Pada Kanwil Kementerian Agama Provinsi Kalimantan Utara
> - **Tagline:** *"Merajut Harmoni, Menjaga Kedamaian di Beranda Batas NKRI Melalui Portal EWS"* / *"Kaltara Aman Kita Mampu"*
> - **Instansi:** Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara / Kantor Kemenag Kabupaten Malinau
> - **Project Leader (Penyusun):** H. Iramsyah Noor, S.Ag., M.A.P. (NDH: 25, NIP: 197205152001121002, Kepala Kantor Kemenag Kab. Malinau)
> - **Sponsor / Mentor:** Prof. Dr. Muhammad Ali Ramdhani, S.TP., M.T. (NIP: 1971106200811009, Kepala BMBPSDM Kemenag RI)
> - **Coach:** Dr. Neneng Maria Kiptyah, M.Pd.
> - **Penguji:** Drs. Seno Hartono, DESS
> - **Lokus Piloting Awal:** Kabupaten Malinau (Cakupan penuh: 5 Kabupaten/Kota dan 55 Kecamatan se-Kalimantan Utara)

## 1. Problem - What problem are we solving?

Kalimantan Utara meraih skor Indeks Kerukunan Umat Beragama (IKUB) tertinggi nasional pada tahun 2025 (**87,48 / Sangat Tinggi**, peringkat 1 nasional), namun menghadapi paradoks antara modal sosial yang kuat dan infrastruktur tata kelola digital EWS di tingkat Kanwil yang belum memadai. Berdasarkan analisis isu strategis metode **ASTRID**, isu prioritas utama (skor tertinggi **30/30**, di atas isu kompetensi digital ASN/Penyuluh 3TP dengan skor 23 dan data rumah ibadah/wakaf spasial dengan skor 21) adalah: **"Belum terintegrasinya EWS deteksi dini konflik keagamaan dan belum optimalnya peran tokoh lintas agama dalam promosi kerukunan digital berbasis ekoteologi pada Kanwil Kemenag Kaltara."**

Tiga akar masalah utama dan kompleksitas strategis yang diselesaikan melalui tiga terobosan (*Nilai*, *Kelembagaan*, dan *Teknologi*):

1. **Kelemahan Sistem Deteksi Dini (Akar Masalah Sistem):** Pelaporan potensi konflik keagamaan sebelumnya bersifat manual, parsial, lisan, dan terlokalisasi per kabupaten/kota tanpa basis data provinsi maupun pemetaan spasial, sehingga respons mitigasi lambat (**3 hingga 7 hari** pascainsiden atau setelah viral).
2. **Kompleksitas Geografis 3TP & Risiko Beranda Batas Negara:** Wilayah Kalimantan Utara seluas **75.467,70 km²** (5 Kabupaten/Kota: Bulungan, Tarakan, Malinau, Nunukan, Tana Tidung; 55 Kecamatan) didominasi jalur sungai dan udara perintis dengan keterbatasan sinyal (*blank spot*). Sebagai beranda batas NKRI yang berbatasan langsung dengan Sabah dan Sarawak (Malaysia) serta dihuni masyarakat multikultural (**6 agama, 11 Lembaga Adat, dan 15 Paguyuban**), wilayah ini rentan terhadap infiltrasi paham ekstremis transnasional dan hoaks SARA.
3. **Optimalisasi Peran SDM & Substansi Ekoteologi (Akar Masalah SDM & Substansi):** Sebanyak **64% masyarakat belum memahami PBM No. 9 dan No. 8 Tahun 2006** (data PKUB). Peran tokoh agama, tokoh adat, penyuluh agama (IPARI), dan pengurus FKUB sebelumnya masih pasif/konvensional pada tatap muka dan belum terwadahi sebagai agen kerukunan digital, sementara narasi kerukunan masih seremonial dan belum mengintegrasikan kearifan **ekoteologi** (merawat kerukunan sejalan dengan merawat alam sebagai amanah bersama) serta partisipasi generasi muda.

**Keterkaitan Kebijakan Makro:**
- **PBM Menteri Agama dan Menteri Dalam Negeri No. 9 & No. 8 Tahun 2006** (pedoman pemeliharaan kerukunan umat beragama, pemberdayaan FKUB, dan pendirian rumah ibadah).
- **Asta Cita Poin ke-8:** Menjamin kebebasan beribadah, memperkuat dialog antariman, memitigasi potensi konflik SARA, dan merawat kerukunan aktif berbasis komunitas/institusi.
- **Asta Protas Kementerian Agama RI:** Meningkatkan Kerukunan dan Cinta Kemanusiaan, Digitalisasi Tata Kelola, Layanan Keagamaan Berdampak, Pendidikan Unggul/Ramah/Terintegrasi, Pemberdayaan Ekonomi Umat, dan Ekoteologi.
- **SDGs 2030:** Berkontribusi pada SDG 16 (Perdamaian, Keadilan, dan Kelembagaan yang Tangguh), SDG 10 (Berkurangnya Kesenjangan di wilayah 3TP), SDG 11 (Kota dan Komunitas yang Berkelanjutan), dan SDG 17 (Kemitraan Multipihak / *Pentahelix*).

## 2. Users - Who is this for?

Sistem menerapkan **Role-Based Access Control (RBAC)** yang membagi pengguna ke dalam **5 kelompok aktor utama** (direpresentasikan dalam 7 nilai `role` pada tabel `users`, ditambah akses publik):

1. **Pelapor Khusus Lapangan (*Field Reporter*)** - role `penyuluh_ipari`, `kua`, `fkub`:
   - **Aktor:** Penyuluh Agama (anggota IPARI), Kepala KUA, Pengurus FKUB, serta Tokoh Agama dan Lembaga Adat.
   - **Hak Akses:** Mengirim laporan pada form "Lapor Dini", penandaan koordinat GPS otomatis, mengunggah berkas bukti dukung (foto, surat PDF, rekaman audio), menyimpan draft laporan saat *offline*, serta memantau riwayat dan status penanganan laporan mandiri.
2. **Masyarakat Terverifikasi (*Verified Citizen*)** - role `masyarakat`:
   - **Aktor:** Umat beragama dan warga terverifikasi di 5 Kabupaten/Kota Kalimantan Utara.
   - **Hak Akses:** Mengirim aduan indikasi awal (masuk ke antrean moderasi verifikator sebelum ditampilkan pada dashboard resmi), mengakses Chatbot "Tanya", direktori rumah ibadah, dan layanan darurat hotline FKUB.
3. **Verifikator / Operator (*Kankemenag Kab/Kota & Kanwil*)** - role `verifikator`:
   - **Aktor:** Petugas operator dan analis pada Kankemenag Kabupaten/Kota serta Kanwil Kemenag Kaltara.
   - **Hak Akses:** Meninjau laporan masuk dan antrean moderasi masyarakat, melakukan verifikasi faktual berjenjang, menyaring dan menolak laporan palsu (`ditolak_hoaks`), menetapkan atau mengubah tingkat kerawanan (`hijau`, `kuning`, `merah`), serta mencatat analisis, rekomendasi mitigasi, dan notulen mediasi (`tindak_lanjut`).
4. **Pimpinan (*Executive Viewer*)** - role `pimpinan`:
   - **Aktor:** Kakanwil Kemenag Kaltara, Kakankemenag Kabupaten/Kota, dan unsur Forkopimda / Bakesbangpol Kaltara.
   - **Hak Akses:** Memantau dashboard agregat kondusivitas provinsi (5 Kabupaten/Kota), melihat peta spasial GIS sebaran kerawanan, dan menerima notifikasi darurat instan (*instant alert*) saat terdapat laporan berstatus `merah` (dengan identitas pelapor tetap terenkripsi/terlindungi).
5. **Super Admin / Pengelola Konten** - role `superadmin`:
   - **Aktor:** Tim Teknis IT (Prakom) dan Pokja Humas/Media Kanwil Kemenag Kaltara.
   - **Hak Akses:** Manajemen akun pengguna multi-role, pengelolaan master data wilayah dan kategori isu, kurasi serta persetujuan publikasi konten di Moderation & Tolerance Media Hub, dan pengelolaan basis pengetahuan FAQ Chatbot (`faq_chatbot`).
6. **Pengunjung Publik (Tanpa Login):**
   - **Hak Akses:** Melihat beranda status kondusivitas wilayah 5 Kabupaten/Kota, mengakses galeri Moderation & Tolerance Media Hub (video, infografis, podcast, naskah khotbah), berkonsultasi dengan Chatbot "Tanya", dan mengakses Pusat Informasi & Layanan Kerukunan (direktori rumah ibadah, agenda lintas iman, hotline FKUB).

## 3. Features - What does the MVP need?

Arsitektur utama mengusung konsep **"Satu Ekosistem, Dua Modul Utama"** (*Digital EWS Terintegrasi* dan *Moderation & Tolerance Media Hub*) yang dijabarkan ke dalam **5 kelompok fitur inti**:

- **Beranda & Status Kondusivitas Wilayah:** Widget ringkasan status 5 Kabupaten/Kota (Bulungan, Tarakan, Malinau, Nunukan, Tana Tidung) dengan indikator warna agregat (`Hijau` = Kondusif, `Kuning` = Potensi Ketegangan / Butuh Mediasi, `Merah` = Ancaman Konflik Mendesak), ticker berita/kabar kerukunan dan agenda lintas iman, serta tombol akses cepat (*Quick Action*) ke "Lapor Dini", "Chatbot Tanya", dan "Hotline FKUB".
- **Modul Digital EWS "Lapor Dini" & Sinkronisasi Offline (PWA):** Form pelaporan lapangan dengan nomor tiket otomatis (`EWS-YYYYMMDD-XXXX`), 5 klasifikasi isu (Pendirian Rumah Ibadah, Gesekan Antarumat/Ormas SARA, Penyiaran Agama/Penistaan, Aliran Keagamaan Baru, Hoaks Transnasional Perbatasan), *auto-geotagging* koordinat GPS (`latitude`, `longitude`), isian judul, tanggal kejadian, kronologi, dan estimasi jumlah pihak terlibat, unggah bukti multi-foto (kompresi JPEG), PDF, atau audio MP3, serta penyimpanan draft lokal (*IndexedDB / LocalStorage*) saat di wilayah *blank spot*.
- **Verifikasi Berjenjang, Tindak Lanjut Mediasi & Whistleblower Protection:** Alur kerja verifikator untuk memeriksa laporan (`draft` -> `terkirim` -> `diverifikasi` -> `proses_mediasi` / `ditolak_hoaks` -> `selesai`), penetapan dan perubahan tingkat kerawanan (`hijau`, `kuning`, `merah`), pencatatan analisis dan rekomendasi mitigasi (`tindak_lanjut`), antrean moderasi khusus untuk aduan masyarakat, serta enkripsi/penyembunyian identitas pelapor dari tampilan publik dan pimpinan umum.
- **Dashboard Pemetaan Spasial GIS & Sistem Notifikasi Cepat (*Instant Alert*):** Visualisasi peta Kalimantan Utara berbasis **Leaflet.js** dengan pin koordinat kejadian sesuai warna kerawanan dan pewarnaan poligon wilayah rawan (`polygon_geojson` / *heat map*), ditambah pengiriman peringatan darurat otomatis ke grup pimpinan dan tim respons cepat melalui Webhook Telegram/WhatsApp ketika laporan berstatus `merah`.
- **Chatbot "Tanya" (Asisten AI & Knowledge Base):** Asisten virtual edukasi peraturan PBM No. 9 & No. 8 Tahun 2006 (prosedur perizinan rumah ibadah, fungsi dan wewenang FKUB), moderasi beragama, ekoteologi, serta klarifikasi penangkal hoaks SARA di perbatasan, lengkap dengan mekanisme pengalihan (*fallback*) langsung ke kontak hotline FKUB.
- **Moderation & Tolerance Media Hub & Bank Naskah Khotbah:** Katalog publikasi terkurasi berisi video testimoni tokoh agama & adat, minimal 10 infografis moderasi, arsip podcast *"Suara Harmoni Perbatasan"*, serta ruang unduh **Bank Naskah Khotbah** / ceramah tematik kerukunan umat dan ekoteologi bagi penyuluh lintas agama, dilengkapi fitur filter berdasarkan agama, tema bahasan, dan format berkas.
- **Pusat Informasi & Layanan Kerukunan:** Direktori geospasial tempat ibadah resmi terverifikasi per kabupaten/kota, kalender agenda bersama dan forum dialog lintas iman, buku saku digital indikator kerawanan konflik perbatasan, serta daftar kontak dan tautan langsung ke sekretariat hotline FKUB se-Kaltara.

## 4. Data - What are we storing?

Basis data relasional (MySQL / PostgreSQL) menyimpan 8 tabel inti sesuai skema pada `PROJECT_BRIEF_PORTAL_BERSATU.md` Bagian 4, ditambah struktur pendukung untuk filter kategori media dan layanan informasi kerukunan:

1. **`wilayah`** (Master wilayah 5 Kabupaten/Kota & 55 Kecamatan se-Kaltara):
   - `id` (PK, INT), `kode_bps` (VARCHAR 10), `nama_kabupaten` (VARCHAR 100), `nama_kecamatan` (VARCHAR 100), `latitude_pusat` (DECIMAL 10,8), `longitude_pusat` (DECIMAL 11,8), `polygon_geojson` (TEXT / JSON).
2. **`users`** (Pengguna sistem multi-role dengan perlindungan identitas pelapor):
   - `id` (PK, BIGINT), `nip_nik` (VARCHAR 30, UNIQUE), `nama_lengkap` (VARCHAR 150), `no_wa` (VARCHAR 20), `email` (VARCHAR 100, UNIQUE), `password_hash` (VARCHAR 255), `role` (ENUM: `superadmin`, `pimpinan`, `verifikator`, `penyuluh_ipari`, `kua`, `fkub`, `masyarakat`), `wilayah_id` (FK `wilayah.id`), `status_aktif` (BOOLEAN, DEFAULT TRUE).
3. **`kategori_isu`** (5 klasifikasi isu laporan EWS):
   - `id` (PK, INT), `nama_kategori` (VARCHAR 100), `deskripsi` (TEXT).
4. **`laporan_ews`** (Data laporan peringatan dini lapangan):
   - `id` (PK, BIGINT), `nomor_tiket` (VARCHAR 50, UNIQUE, format `EWS-YYYYMMDD-XXXX`), `pelapor_id` (FK `users.id`), `wilayah_id` (FK `wilayah.id`), `alamat_detail` (TEXT), `latitude` (DECIMAL 10,8), `longitude` (DECIMAL 11,8), `kategori_id` (FK `kategori_isu.id`), `judul_laporan` (VARCHAR 255), `kronologi` (TEXT), `status_kerawanan` (ENUM: `hijau`, `kuning`, `merah`), `status_laporan` (ENUM: `draft`, `terkirim`, `diverifikasi`, `proses_mediasi`, `selesai`, `ditolak_hoaks`), `created_at` (TIMESTAMP), `updated_at` (TIMESTAMP).
   - *Catatan penyelarasan spesifikasi form Bagian 3.B:* Form pelaporan lapangan juga membutuhkan atribut tanggal kejadian dan estimasi jumlah pihak terlibat (dapat disimpan sebagai kolom tambahan `tanggal_kejadian` dan `estimasi_pihak_terlibat` pada `laporan_ews`).
5. **`bukti_laporan`** (Lampiran berkas bukti dukung laporan EWS):
   - `id` (PK, BIGINT), `laporan_id` (FK `laporan_ews.id`), `file_path` (VARCHAR 255), `tipe_file` (VARCHAR 50: `image/jpeg`, `application/pdf`, `audio/mp3`), `caption` (VARCHAR 255).
6. **`tindak_lanjut`** (Riwayat analisis verifikator, mediasi, dan perubahan status risiko):
   - `id` (PK, BIGINT), `laporan_id` (FK `laporan_ews.id`), `petugas_id` (FK `users.id`), `catatan_analisis` (TEXT), `rekomendasi_mitigasi` (TEXT), `perubahan_status_kerawanan` (ENUM: `hijau`, `kuning`, `merah`), `tanggal_penanganan` (DATETIME).
7. **`media_hub`** (Katalog konten moderasi beragama, podcast, infografis, dan naskah khotbah):
   - `id` (PK, INT), `judul` (VARCHAR 255), `slug` (VARCHAR 255, UNIQUE), `tipe_konten` (ENUM: `video`, `infografis`, `podcast`, `naskah_khotbah`), `url_file` (VARCHAR 255), `thumbnail_url` (VARCHAR 255), `deskripsi` (TEXT), `tokoh_narasumber` (VARCHAR 150), `status_tampil` (BOOLEAN, DEFAULT TRUE), `published_at` (TIMESTAMP).
   - *Catatan penyelarasan ERD & Bagian 3.D:* ERD mencantumkan relasi `MEDIA_HUB ||--o{ KATEGORI_MEDIA : "kategori"` dan Bagian 3.D mensyaratkan filter berdasarkan agama serta tema bahasan.
8. **`faq_chatbot`** (Basis pengetahuan Chatbot "Tanya"):
   - `id` (PK, INT), `pertanyaan_kunci` (VARCHAR 255), `jawaban` (TEXT), `kategori` (VARCHAR 100: `pbm_2006`, `moderasi`, `ekoteologi`, `perbatasan`).
9. **Data Pusat Informasi & Layanan Kerukunan (Bagian 3.E):**
   - Direktori geospasial rumah ibadah resmi terverifikasi per kabupaten/kota, agenda kegiatan/dialog lintas iman, dan kontak sekretariat hotline FKUB se-Kaltara.

## 5. Tech - What stack are we using?

- **Backend Runtime & Framework:** PHP (`^7.4 || ^8.0`) dengan **Slim Framework 4** (`slim/slim`, `slim/psr7`), **PHP-DI 6** untuk dependency injection container, dan **Monolog 2** untuk logging aplikasi.
- **Database:** Basis data relasional **MySQL** atau **PostgreSQL** (dengan penyimpanan poligon GeoJSON dan koordinat spasial `DECIMAL(10,8)` / `DECIMAL(11,8)`).
- **Frontend & Progressive Web App (PWA):** Portal web responsif mobile-first berbasis **PWA** (`manifest.json`, Service Worker untuk caching aset/halaman offline, serta `IndexedDB` / `LocalStorage` untuk antrean draft laporan saat tanpa sinyal dan kompresi gambar di sisi klien).
- **Pemetaan Spasial (GIS):** **Leaflet.js** untuk visualisasi peta interaktif Kalimantan Utara, marker pin kejadian (`Hijau`, `Kuning`, `Merah`), dan layer poligon batas wilayah GeoJSON (*heat map*).
- **Integrasi Notifikasi Darurat (*Instant Alert*):** Webhook HTTP ke Bot **Telegram** dan/atau **WhatsApp** untuk pengiriman otomatis peringatan laporan berstatus `merah`.
- **Quality & Testing Tools:** **PHPUnit 9** (`composer test`) dengan Prophecy (`phpspec/prophecy-phpunit`), **PHP_CodeSniffer** PSR-12 (`vendor/bin/phpcs`), dan **PHPStan** level 4 (`vendor/bin/phpstan`).

## 6. Monetize - How will this make money?

**Zero cost bagi pengguna (Akses Gratis untuk Semua).** Berdasarkan strategi *Marketing 4P + 1C* (Slide 12), Portal BERSATU adalah layanan publik pemerintah nirlabal pada Kanwil Kementerian Agama Provinsi Kalimantan Utara. Seluruh fitur pelaporan EWS, edukasi moderasi, dan peta kerukunan dapat diakses gratis oleh aparatur, penyuluh, tokoh agama/adat, dan masyarakat, dengan pembiayaan pengembangan serta operasional didukung sumber pembiayaan proyek/instansi yang relevan.

## 7. UI/UX - How should this look and feel?

- **Karakter Visual & Prinsip Desain:** Responsif *mobile-first* dan ramah kondisi lapangan 3TP (ringan, hemat kuota, dapat di-*install* ke layar utama ponsel sebagai PWA, dan tetap berfungsi menyimpan draft saat *offline*). Identitas visual mencerminkan semangat harmoni Kementerian Agama dan keberagaman Kalimantan Utara, dengan kode warna status kerawanan yang tegas dan konsisten di seluruh layar:
  - **Hijau (`hijau`):** Kondusif - peristiwa tertangani/diredam, tensi sosial normal.
  - **Kuning (`kuning`):** Potensi Ketegangan / Butuh Mediasi - ada friksi horizontal atau sengketa izin yang memerlukan mediasi segera oleh penyuluh/KUA/FKUB.
  - **Merah (`merah`):** Ancaman Konflik Mendesak - eskalasi tinggi, potensi mobilisasi massa/kekerasan fisik, butuh intervensi darurat pimpinan dan aparat keamanan.
- **Layar dan Rute Utama:**
  - `/` (Beranda & Status Kondusivitas Wilayah) - ringkasan indikator warna 5 Kabupaten/Kota (Bulungan, Tarakan, Malinau, Nunukan, Tana Tidung), ticker berita kerukunan & agenda lintas iman, serta *Quick Action* ke "Lapor Dini", "Chatbot Tanya", dan "Hotline FKUB".
  - `/auth/login` (Autentikasi Multi-Role) - halaman masuk berdasarkan kredensial pengguna (`nip_nik` / `email`) untuk mengakses fitur sesuai peran RBAC.
  - `/ews/lapor` (Form "Lapor Dini" & Draft Offline) - formulir pelaporan lapangan dengan tangkapan koordinat GPS otomatis, pilihan 5 kategori isu, unggah bukti (foto/PDF/audio), penyimpanan draft lokal saat *offline*, dan daftar riwayat tiket pelapor.
  - `/ews/verifikasi` (Meja Kerja Verifikator & Moderasi Aduan) - antrean verifikasi laporan lapangan dan moderasi aduan masyarakat, penetapan/perubahan status kerawanan (`hijau`, `kuning`, `merah`) serta status laporan (`diverifikasi`, `proses_mediasi`, `selesai`, `ditolak_hoaks`), dan input catatan tindak lanjut/mediasi.
  - `/ews/peta-gis` (Dashboard Eksekutif & Pemetaan GIS) - peta interaktif Leaflet.js se-Kalimantan Utara dengan layer GeoJSON 5 Kabupaten/Kota, pin kejadian berwarna, statistik agregat pimpinan, dan log peringatan darurat.
  - `/media-hub` (Moderation & Tolerance Media Hub & Bank Naskah Khotbah) - galeri video tokoh agama/adat, infografis moderasi, podcast *"Suara Harmoni Perbatasan"*, dan ruang unduh naskah khotbah bertema kerukunan/ekoteologi dengan filter agama, tema, dan format.
  - `/tanya` (Chatbot "Tanya" AI & Knowledge Base) - antarmuka tanya-jawab interaktif seputar PBM No. 9 & 8 Tahun 2006, moderasi beragama, ekoteologi, dan klarifikasi hoaks SARA perbatasan, dilengkapi tombol *fallback* langsung ke hotline FKUB.
  - `/informasi-kerukunan` (Pusat Informasi & Layanan Kerukunan) - direktori geospasial rumah ibadah resmi terverifikasi per kabupaten/kota, kalender agenda lintas iman, buku saku digital indikator kerawanan, dan daftar kontak sekretariat FKUB se-Kaltara.
  - `/admin` (Panel Super Admin) - manajemen pengguna multi-role, master wilayah & kategori isu, kurasi konten Media Hub, dan pengelolaan FAQ Chatbot.

## 8. Deployment - Where and how will this ship?

- **Target tahapan rilis (Timeline 60 Hari / 8 Minggu - Slide 7 & Bagian 6 Brief):**
  - **Minggu 1-2 (Tahap 1 - Persiapan & Tata Kelola):** Setup repositori, migrasi DDL basis data, master data 5 Kab/Kota + GeoJSON, dan autentikasi RBAC.
  - **Minggu 3-5 (Tahap 2 - Pengembangan Sistem Digital):** Implementasi PWA, modul EWS "Lapor Dini", GIS Leaflet.js, Webhook instant alert, Media Hub, dan Chatbot "Tanya".
  - **Minggu 6-7 (Tahap 3 - Edukasi & Bimtek):** Uji coba dan pendampingan teknis bersama Penyuluh Agama (IPARI) serta pengurus FKUB.
  - **Minggu 8 (Tahap 4 - Piloting, Launching & Evaluasi):** Uji coba lapangan (*piloting*) di lokus percontohan **Kabupaten Malinau** dan rilis operasional pada domain resmi Kanwil Kemenag Kaltara (diikuti replikasi penuh ke 5 Kabupaten/Kota pada jangka menengah 6 bulan).
- **Lingkungan Runtime:** Aplikasi PHP 7.4/8.x (Slim Framework 4) dengan document root diarahkan ke `public/`, direktori `logs/` dan `var/cache/` yang *writable*, serta dukungan kontainer pengembangan melalui `docker-compose.yml` (`php -S 0.0.0.0:8080 -t public`).
- **Kebutuhan Konfigurasi Environment (`> TODO` nama variabel final saat implementasi):** Koneksi database MySQL/PostgreSQL, kunci enkripsi identitas pelapor, token/URL Webhook Bot Telegram/WhatsApp untuk *instant alert*, dan direktori penyimpanan berkas unggahan (`bukti_laporan` serta `media_hub`).

## 9. Usage model and constraints (optional)

- **Cakupan Operasional & Skala Wilayah:** Melayani **5 Kabupaten/Kota dan 55 Kecamatan** di Provinsi Kalimantan Utara (luas 75.467,70 km²), dengan lokus piloting awal di **Kabupaten Malinau**.
- **Kondisi Jaringan Lapangan (Offline-First untuk Pelaporan):** Banyak kecamatan pedalaman/perbatasan berada di wilayah 3TP dengan sinyal seluler minim atau *blank spot*. Fitur "Lapor Dini" wajib mendukung penyimpanan sementara di perangkat (`IndexedDB` / `LocalStorage`) melalui PWA dan sinkronisasi saat kembali terhubung ke internet, serta kompresi unggahan foto agar hemat bandwidth.
- **Keamanan & Perlindungan Identitas Pelapor (*Whistleblower Protection*):** Data identitas pelapor lapangan wajib dienkripsi pada tingkat basis data dan disembunyikan dari tampilan publik maupun tampilan pimpinan umum untuk mencegah intimidasi di lapangan.
- **Penyaringan Hoaks & Moderasi Berjenjang:** Jalur pelaporan cepat "Lapor Dini" diprioritaskan bagi pelapor khusus terverifikasi (`penyuluh_ipari`, `kua`, `fkub`), sedangkan laporan indikasi awal dari jalur `masyarakat` wajib melewati antrean moderasi `verifikator` sebelum dipetakan ke dashboard GIS resmi. Laporan yang terbukti palsu ditandai sebagai `ditolak_hoaks`.
- **Standarisasi Indikator Kerawanan:** Mengacu pada 3 tingkat status baku (`hijau` = Kondusif, `kuning` = Potensi Ketegangan / Butuh Mediasi, `merah` = Ancaman Konflik Mendesak dengan pemicu otomatis *instant alert* ke pimpinan dan tim respons cepat).
