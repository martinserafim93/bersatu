# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

- **Pelapor Khusus Lapangan (`penyuluh_ipari`, `kua`, `fkub`):** Penyuluh Agama (IPARI), Kepala KUA, Pengurus FKUB, serta Tokoh Agama dan Lembaga Adat di Kalimantan Utara yang membutuhkan sarana pelaporan dini bertanda GPS, lampiran bukti (foto terkompresi, PDF, audio MP3), dan antrean draf luring saat bertugas di wilayah perbatasan maupun pedalaman 3TP tanpa sinyal.
- **Masyarakat Terverifikasi (`masyarakat`):** Warga di 5 Kabupaten/Kota Kalimantan Utara yang mendaftar mandiri dan diaktifkan oleh `superadmin` untuk menyampaikan aduan indikasi awal potensi ketegangan (diproses melalui antrean moderasi `verifikator`) serta mengakses layanan informasi kerukunan.
- **Verifikator / Operator (`verifikator`):** Petugas Kankemenag Kabupaten/Kota dan Kanwil Kemenag Kaltara yang memeriksa laporan masuk dan antrean aduan masyarakat, memverifikasi fakta (termasuk membuka identitas pelapor terenkripsi untuk konfirmasi lapangan), menyaring laporan palsu (`ditolak_hoaks`), menetapkan tingkat kerawanan (`hijau`, `kuning`, `merah`), dan mencatat tindak lanjut mediasi.
- **Pimpinan (`pimpinan`):** Kakanwil Kemenag Kaltara, Kakankemenag Kabupaten/Kota, serta unsur Forkopimda/Bakesbangpol yang memantau ringkasan kondusivitas 5 Kabupaten/Kota, peta spasial detail, dan notifikasi darurat instan setelah laporan diverifikasi berstatus `merah` tanpa membuka identitas pribadi pelapor.
- **Super Admin / Pengelola Konten (`superadmin`):** Pokja Teknis IT (Prakom) dan Pokja Humas/Media Kanwil Kemenag Kaltara yang memverifikasi pendaftaran akun pengguna, mengelola master wilayah/GeoJSON dan kategori isu, serta mengkurasi konten Media Hub, FAQ Chatbot, direktori rumah ibadah, agenda lintas iman, dan kontak FKUB. Tidak mengakses atau mengelola isi laporan EWS.
- **Pengunjung Publik (Anonim):** Masyarakat umum yang mengakses beranda kondusivitas wilayah dan peta kerukunan tingkat kecamatan berlabel netral, galeri Media Hub, Bank Naskah Khotbah, Chatbot "Tanya", dan Pusat Informasi Kerukunan tanpa memerlukan akun.

## Product Purpose

Portal BERSATU dibangun pada Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara (Proyek Perubahan PKN Tk. II Angkatan XXX Tahun 2026) untuk mengatasi lambatnya pelaporan dan mitigasi potensi konflik keagamaan yang sebelumnya manual, lisan, dan tersebar per kabupaten/kota (respons 3 hingga 7 hari), sekaligus menjawab rendahnya pemahaman masyarakat atas regulasi PBM No. 9 dan No. 8 Tahun 2006 (64% menurut data PKUB) serta kerentanan hoaks SARA lintas batas Sabah-Sarawak. Keberhasilan produk diukur dari terwujudnya deteksi dini terpadu secara *real-time* di 5 Kabupaten/Kota dan 55 Kecamatan se-Kalimantan Utara (lokus *piloting* awal Kabupaten Malinau), terlindunginya identitas pelapor, tersampaikannya informasi publik yang menenangkan tanpa memicu kepanikan, serta terselenggaranya promosi moderasi beragama dan ekoteologi yang dapat dijangkau hingga wilayah 3TP.

## Positioning

Portal BERSATU memadukan tiga komponen yang sebelumnya terpisah di pemerintahan daerah: mekanisme *Early Warning System* (EWS) berjenjang dengan *whistleblower protection* terenkripsi dan penyaringan hoaks, pemetaan spasial dua lapis yang memisahkan terminologi kerja internal dan label publik netral, serta ekosistem edukasi aktif moderasi beragama dan ekoteologi (Media Hub, Bank Naskah Khotbah, dan Chatbot "Tanya" PBM 2006). Ketahanan operasi offline-first pada level browser memungkinkan agen lapangan di wilayah sungai/udara perintis tetap bekerja tanpa sinyal.

## Operating Context

- **Instansi & Lingkup:** Kantor Wilayah Kementerian Agama Provinsi Kalimantan Utara dan Kankemenag Kabupaten Malinau (lokus *piloting* awal), berkoordinasi dengan FKUB, KUA, IPARI, lembaga adat, serta Forkopimda/Bakesbangpol.
- **Cakupan Geografis:** 5 Kabupaten/Kota (Kabupaten Malinau, Kabupaten Bulungan, Kota Tarakan, Kabupaten Nunukan, Kabupaten Tana Tidung) dan 55 Kecamatan dengan total luas 75.467 km2, mencakup wilayah pedalaman 3TP (*blank spot*) dan kawasan perbatasan Indonesia-Malaysia.
- **Lingkungan Perangkat:** Mayoritas agen lapangan dan warga menggunakan peramban ponsel Android pada jaringan seluler lemah atau terputus-putus; petugas verifikator dan pimpinan juga memantau melalui peramban desktop kantor.

## Capabilities and Constraints

- **Otorisasi & Privasi Pelapor:** Pendaftaran akun publik (`masyarakat`, `penyuluh_ipari`, `kua`, `fkub`) berstatus `menunggu_verifikasi` hingga disetujui `superadmin` (yang dapat mengoreksi peran). Identitas sensitif (`nip_nik_encrypted`, `no_wa_encrypted`) diamankan dengan `AES-256-GCM` + *blind index* `HMAC-SHA256`; hanya `verifikator` aktif dan pemilik akun/tiket yang dapat membuka identitas pelapor, sedangkan `superadmin` terisolasi dari data laporan EWS dan `pimpinan`/publik tidak menerima identitas pribadi pelapor.
- **Moderasi & Label Publik Netral:** Laporan `draft`, `terkirim`, dan `ditolak_hoaks` tidak pernah ditampilkan ke publik. Label indikator publik dikunci netral: `hijau` -> *Kondusif & Harmonis*, `kuning` -> *Dalam Fasilitasi Dialog & Pembinaan*, `merah` -> *Dalam Koordinasi Intensif Lintas Sektor*. Notifikasi darurat Telegram/WhatsApp hanya dipicu sesudah verifikasi menetapkan status `merah`.
- **Ketahanan Offline & Target Hosting:** Berjalan di atas **PHP 8.4**, **Slim Framework 4**, **MySQL (`PDO`)**, server-rendered PHP templates tanpa bundler Node.js, Leaflet.js untuk GIS, serta PWA (`manifest.json`, Service Worker, `IndexedDB`/`LocalStorage`, kompresi JPEG klien). Wajib patuh pada batas lingkungan hosting target InfinityFree (ukuran tiap berkas `.php`/`.js`/`.css` di bawah 1 MB, unggahan di bawah 10 MB, sinkronisasi langsung dari sesi browser pengguna, dan dukungan relay webhook Telegram via Cloudflare Worker).
- **Di Luar Ruang Lingkup MVP:** Dokumen tata kelola non-perangkat lunak (SK, SOP cetak, buku saku, MoU/PKS, Bimtek tatap muka, laporan evaluasi), pengiriman konten mandiri oleh kreator muda, dan integrasi AI LLM berbayar tidak termasuk dalam MVP awal.

## Brand Commitments

- **Nama Resmi:** Portal BERSATU (*BERsama Saling jAga Toleransi Umat*); nama singkat/ikon PWA: **BERSATU** (*Beragama - Bersinergi - Harmoni*).
- **Tagline Resmi:** *"Merajut Harmoni, Menjaga Kedamaian di Beranda Batas NKRI Melalui Portal EWS"*.
- **Integritas Data & Komunikasi Publik:** Bahasa Indonesia baku yang menenangkan, informatif, inklusif lintas umat beragama dan lembaga adat, serta tidak pernah menayangkan data laporan mentah atau spekulatif di permukaan publik.

## Evidence on Hand

- **Dokumen & Data Terstruktur di Repositori:** `blueprint/project-plan.md`, `blueprint/context/project-overview.md`, skema 12 tabel MySQL (`src/Infrastructure/Persistence/Database/SchemaDefinition.php`), serta data seeder master 55 Kecamatan Kaltara beserta batas GeoJSON dan 5 kategori isu EWS.
- **Data Rujukan Proyek Perubahan:** Peringkat #1 IKUB Nasional 2025 untuk Kalimantan Utara, skor prioritas isu ASTRID 30/30, serta angka PKUB 64% masyarakat yang belum memahami PBM No. 9 & No. 8 Tahun 2006.
- **Aset Visual Saat Ini:** Ikon PWA vektor awal (`public/assets/icons/icon-192.svg` dan `public/assets/icons/icon-512.svg`). Logo resmi Kemenag RI/FKUB, foto dokumentasi lapangan, serta berkas infografis/audio/video Media Hub terkurasi **belum tersedia di repositori** dan tidak boleh dibuat secara fiktif.

## Product Principles

1. **Ketertiban Informasi Publik tanpa Menimbulkan Kepanikan:** Permukaan publik hanya menyajikan data agregat terverifikasi dengan istilah dialogis dan netral.
2. **Perlindungan Pelapor & Pemisahan Wewenang Ketat:** Enkripsi identitas dan pemisahan tugas antarperan (`verifikator`, `superadmin`, `pimpinan`, pelapor) dijaga di seluruh alur antarmuka dan API.
3. **Andal di Jaringan Terbatas & Wilayah 3TP:** Antarmuka harus ringan, cepat dimuat di perangkat Android kelas menengah, dan tangguh menghadapi kondisi *offline*.
4. **Kolaborasi Lintas Iman Berakar Ekoteologi:** Layanan informasi, media edukasi, dan panduan regulasi memosisikan kerukunan dan perawatan alam sebagai tanggung jawab bersama.

## Accessibility & Inclusion

- Memenuhi keterbacaan dan kontras minimal **WCAG 2.1 Level AA** agar tetap jelas dibaca baik di ruang kerja maupun di bawah cahaya luar ruang lapangan 3TP.
- Mengoptimalkan ukuran aset dan interaksi untuk ponsel layar sentuh Android, mendukung navigasi keyboard penuh, fokus terlihat, serta menghormati preferensi *reduced motion*.
- Menggunakan Bahasa Indonesia baku yang netral, mudah dipahami oleh warga lintas latar belakang agama, adat, dan tingkat literasi digital.
