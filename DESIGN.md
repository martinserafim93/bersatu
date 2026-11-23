---
name: Portal BERSATU
description: Papan Maklumat Harmoni - Sistem Desain Web & PWA Early Warning System Kerukunan Umat Beragama Kanwil Kemenag Kalimantan Utara
colors:
  zamrud-rimba: "#0b5d46"
  zamrud-rimba-gelap: "#073f2f"
  emas-tenun: "#b8841e"
  emas-tenun-lembut: "#f5edd6"
  kertas-kapas-hangat: "#f6f5ef"
  permukaan-kartu: "#ffffff"
  kanvas-berlapis: "#eceae0"
  tinta-damar: "#12241c"
  tinta-rimba: "#334e42"
  garis-halus: "#d5d2c4"
  garis-tegas: "#9fb4a9"
  status-hijau-latar: "#e6f4ed"
  status-hijau-tepi: "#9bd0b5"
  status-hijau-teks: "#0b5239"
  status-hijau-titik: "#12805c"
  status-kuning-latar: "#fef5e1"
  status-kuning-tepi: "#e6c373"
  status-kuning-teks: "#6e4805"
  status-kuning-titik: "#c48410"
  status-merah-latar: "#fcebe9"
  status-merah-tepi: "#e5a49e"
  status-merah-teks: "#7a1c14"
  status-merah-titik: "#c9372c"
typography:
  display:
    fontFamily: 'Georgia, "Cambria", "Times New Roman", serif'
    fontSize: "clamp(1.85rem, 3.6vw, 2.75rem)"
    fontWeight: 700
    lineHeight: 1.15
    letterSpacing: "-0.025em"
  headline:
    fontFamily: 'Georgia, "Cambria", "Times New Roman", serif'
    fontSize: "clamp(1.38rem, 2.4vw, 1.85rem)"
    fontWeight: 700
    lineHeight: 1.2
    letterSpacing: "-0.025em"
  title:
    fontFamily: '"Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, Roboto, "Helvetica Neue", Arial, sans-serif'
    fontSize: "1.05rem"
    fontWeight: 700
    lineHeight: 1.3
    letterSpacing: "-0.02em"
  body:
    fontFamily: '"Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, Roboto, "Helvetica Neue", Arial, sans-serif'
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.6
    letterSpacing: "0em"
  label:
    fontFamily: '"Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, Roboto, "Helvetica Neue", Arial, sans-serif'
    fontSize: "0.82rem"
    fontWeight: 700
    lineHeight: 1.3
    letterSpacing: "0.03em"
rounded:
  control: "8px"
  card: "12px"
  pill: "999px"
spacing:
  xs: "8px"
  sm: "12px"
  md: "16px"
  lg: "20px"
  xl: "28px"
  2xl: "40px"
components:
  button-primary:
    backgroundColor: "{colors.zamrud-rimba}"
    textColor: "{colors.permukaan-kartu}"
    rounded: "{rounded.control}"
    padding: "0.65rem 1.1rem"
  button-primary-hover:
    backgroundColor: "{colors.zamrud-rimba-gelap}"
  button-secondary:
    backgroundColor: "{colors.permukaan-kartu}"
    textColor: "{colors.zamrud-rimba}"
    rounded: "{rounded.control}"
    padding: "0.65rem 1.1rem"
  button-gold:
    backgroundColor: "{colors.emas-tenun}"
    textColor: "{colors.tinta-damar}"
    rounded: "{rounded.control}"
    padding: "0.65rem 1.1rem"
  card-surface:
    backgroundColor: "{colors.permukaan-kartu}"
    textColor: "{colors.tinta-damar}"
    rounded: "{rounded.card}"
    padding: "1.2rem"
  input-field:
    backgroundColor: "{colors.permukaan-kartu}"
    textColor: "{colors.tinta-damar}"
    rounded: "{rounded.control}"
    padding: "0.62rem 0.78rem"
  status-pill-hijau:
    backgroundColor: "{colors.status-hijau-latar}"
    textColor: "{colors.status-hijau-teks}"
    rounded: "{rounded.pill}"
    padding: "0.35rem 0.7rem"
  nav-header:
    backgroundColor: "{colors.zamrud-rimba-gelap}"
    textColor: "{colors.kertas-kapas-hangat}"
    padding: "0.85rem 0"
---

# Design System: Portal BERSATU

## Overview

**Creative North Star: "Papan Maklumat Harmoni (The Civic Harmony Ledger)"**

Portal BERSATU dirancang seperti papan maklumat dan buku induk sipil yang berwibawa, tenang, dan mudah dibaca dari pesisir hingga pedalaman 3TP Kalimantan Utara. Bidang datar bernuansa kertas hangat dan hijau hutan-sungai menggantikan hiruk-pikuk aplikasi darurat komersial sehingga publik merasakan kepastian tata kelola tanpa kepanikan.

Sistem ini bekerja pada empat mode permukaan tanpa kehilangan jati diri yang sama: `Persuade` pada beranda maklumat publik (`/`), `Operate` pada halaman tugas terfokus (masuk, daftar, Lapor Dini, meja verifikator, dan panel admin), serta `Read` dan `Experience` untuk pusat edukasi dan galeri Media Hub. Seluruh antarmuka memakai render PHP di sisi server dengan satu berkas stylesheet token murni di bawah 1 MB agar cepat dimuat pada jaringan seluler lemah dan patuh pada batas hosting InfinityFree.

Anti-referensi yang ditolak secara tegas adalah gaya panel bertumpuk bayangan (ghost card), pita warna tebal di sisi kartu, emoji sebagai ikon UI, teks judul bergaya pemasaran dengan kicker atau eyebrow di atas heading, serta pewarnaan merah menyala yang sensasional pada status publik.

**Key Characteristics:**
- Palet hijau hutan Kalimantan Utara dan kertas hangat ber-tint zamrud tanpa abu-abu netral mati
- Permukaan datar berlapis tonal dan garis tepi seragam 1px tanpa drop-shadow
- Tipografi kontras tinggi memakai stack sistem dan serif klasik tanpa unduhan webfont berat
- Penanda status kerawanan dua lapis dengan titik warna netral dan label publik yang menenangkan
- Kepatuhan aksesibilitas WCAG 2.1 AA, target sentuh ramah Android, dan mode hemat offline 3TP

## Colors

Palet memadukan wibawa kelembagaan hijau zamrud hutan Kalimantan Utara dengan bidang kertas hangat bernuansa netral lembut serta aksen emas tenun yang hemat dan fokus.

### Primary
- **Zamrud Rimba** (`#0b5d46`): Warna identitas utama untuk bidang hero beranda, tombol aksi primer, tautan aktif, dan indikator fokus aksesibilitas.
- **Zamrud Rimba Gelap** (`#073f2f`): Digunakan untuk header navigasi, footer kelembagaan, panel ringkasan hero, dan status hover tombol primer.

### Secondary
- **Emas Tenun** (`#b8841e`): Aksen hangat untuk tombol tindakan utama pada latar gelap (seperti tombol pendaftaran dan Lapor Dini di hero) dengan teks tinta gelap agar kontras tetap terjaga.
- **Emas Tenun Lembut** (`#f5edd6`): Latar lencana kecil penanda lokus piloting Kabupaten Malinau.

### Tertiary
- **Latar / Tepi / Teks / Titik Hijau Kondusif** (`#e6f4ed`, `#9bd0b5`, `#0b5239`, `#12805c`): Penanda status *Kondusif & Harmonis*, kotak pesan sukses, dan aksen catatan ekoteologi.
- **Latar / Tepi / Teks / Titik Kuning Pembinaan** (`#fef5e1`, `#e6c373`, `#6e4805`, `#c48410`): Penanda status *Dalam Fasilitasi Dialog & Pembinaan*, pita peringatan jaringan offline 3TP, serta informasi status akun `menunggu_verifikasi`.
- **Latar / Tepi / Teks / Titik Merah Koordinasi** (`#fcebe9`, `#e5a49e`, `#7a1c14`, `#c9372c`): Penanda status *Dalam Koordinasi Intensif Lintas Sektor* dan umpan balik galat validasi maupun gagal masuk.

### Neutral
- **Kertas Kapas Hangat** (`#f6f5ef`): Kanvas dasar halaman dan teks terang di atas bidang Zamrud Rimba.
- **Kanvas Berlapis** (`#eceae0`): Permukaan seksi berselang-seling seperti pita ticker agenda dan seksi Aksi Cepat.
- **Permukaan Kartu** (`#ffffff`): Permukaan bersih untuk kartu kabupaten, kartu aksi, kartu media, panel formulir, dan kolom input.
- **Tinta Damar** (`#12241c`): Teks judul dan isi utama berkontras tinggi (> 13:1 di atas kanvas terang).
- **Tinta Rimba** (`#334e42`): Teks deskripsi, metadata, petunjuk formulir, dan catatan kaki bernuansa zamrud.
- **Garis Halus & Garis Tegas** (`#d5d2c4`, `#9fb4a9`): Pembatas struktural `1px` untuk kartu dan seksi (`#d5d2c4`) serta tepi kolom input dan tombol sekunder (`#9fb4a9`).

### Named Rules
**The Calm Frontier Palette Rule.** Semua teks sekunder dan border netral harus tetap ditarik dari rona hijau-hangat zamrud, bukan abu-abu netral polos; warna emas tenun dibatasi sebagai aksen CTA utama atau penanda piloting agar fokus publik tidak terpecah.

**The Neutral Signal Restraint Rule.** Warna status hijau, kuning, dan merah pada layar publik hanya hadir di dalam pil status dan titik indikator 8px beserta latar lembutnya, tidak pernah memenuhi seluruh kartu atau disertai istilah panik.

## Typography

**Display Font:** `Georgia, "Cambria", "Times New Roman", serif`
**Body Font:** `"Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, Roboto, "Helvetica Neue", Arial, sans-serif`

**Character:** Perpaduan serif klasik yang tenang dan tepercaya untuk maklumat wilayah bersama sans-serif sistem bawaan perangkat agar halaman tetap tajam, netral, dan instan dimuat tanpa unduhan berkas font eksternal.

### Hierarchy
- **Display** (700, `clamp(1.85rem, 3.6vw, 2.75rem)`, 1.15): Judul hero utama di beranda (`letter-spacing: -0.025em`, `text-wrap: balance`).
- **Headline** (700, `clamp(1.38rem, 2.4vw, 1.85rem)`, 1.2): Judul seksi halaman dan judul utama shell kerja `Operate` (`1.65rem`).
- **Title** (700, `1.05rem`, 1.3): Nama Kabupaten/Kota, judul kartu Aksi Cepat, dan judul item Media Hub.
- **Body** (400, `1rem` / 16px, 1.6): Paragraf maklumat dan deskripsi formulir, dibatasi lebar bacaan `max-width: 68ch`.
- **Label** (700, `0.78rem` hingga `0.9rem`, 1.3, `letter-spacing: 0.03em`): Label pil status, lencana peran, metadata media huruf kapital, serta penanda `tabular-nums` untuk angka dan tanggal.

### Named Rules
**The Zero-Webfont 3TP Budget Rule.** Gunakan Georgia serif untuk judul berwibawa dan system-ui sans-serif untuk teks kerja agar nol permintaan font eksternal di jaringan lambat 3TP, disertai tabular-nums wajib pada seluruh hitungan kecamatan, laporan, dan tanggal.

**The Direct Heading Hierarchy Rule.** Setiap heading utama langsung berbicara melalui bobot dan proporsinya sendiri tanpa label kicker atau eyebrow di atasnya.

## Layout

Seluruh layar memakai pembungkus terpusat berlebar maksimum `1160px` dengan bantalan samping `1.25rem` (`20px`) dan ritme vertikal seksi `2.5rem` (`40px`). Beranda `Persuade` membuka dengan pita ticker agenda diikuti grid hero asimetris (`1fr` di seluler, `1.25fr 0.85fr` dengan jarak `2.5rem` mulai lebar `900px`), lalu grid kartu adaptif (`repeat(auto-fit, minmax(210px..240px, 1fr))`, jarak `1rem`). Layar tugas `Operate` memusatkan panel formulir tunggal di dalam wadah berlebar maksimum `560px` (bantalan `1.75rem`) dengan tata letak vertikal berurutan satu kolom agar nyaman di layar ponsel lapangan. Breakpoint responsif tercatat pada `760px` (footer dua kolom), `820px` (baris ticker horisontal), `900px` (split hero), dan `960px` (menu navigasi utama horisontal).

## Elevation & Depth

Permukaan Portal BERSATU menerapkan pendekatan datar berlapis tonal penuh tanpa `box-shadow` sama sekali. Kedalaman hirarki antar elemen diciptakan lewat selisih latar hangat (kanvas `#f6f5ef` menuju pita berlapis `#eceae0` dan kartu putih `#ffffff`) serta garis pembatas seragam `1px solid`. Bidang header dan hero berlatar Zamrud Rimba Gelap dan Zamrud Rimba menambatkan bobot visual paling kuat di bagian atas halaman.

### Named Rules
**The Flat-By-Default Rule.** Semua permukaan berada pada posisi datar tanpa box-shadow. Kedalaman dibangun semata melalui urutan tonal bidang dan garis tepi seragam 1px, dengan umpan balik hover berupa pergeseran warna border atau geser vertikal mikro -2px pada kartu aksi.

**The Uniform Edge Rule.** Setiap kartu, callout, dan umpan balik formulir wajib memakai border 1px merata di keempat sisi; dilarang memakai aksen border-left atau border-right berwarna tebal lebih dari 1px.

## Shapes

Bahasa bentuk membagi sudut ke dalam tiga tingkat fungsional yang tegas: sudut membulat `12px` untuk seluruh kartu konten dan panel formulir, sudut `8px` untuk komponen kendali interaktif (tombol, kolom input, pilihan dropdown, tautan menu, wadah ikon), serta kapsul penuh (`999px`) khusus untuk pil hero, lencana piloting, dan pil status kondusivitas. Seluruh kartu berbingkai datar dengan garis batas tipis tanpa sudut memotong atau bentuk dekoratif berlebih.

## Components

Komponen dirancang tegas, tenang, dan mudah disentuh pengguna lapangan (`tactile & dignified`), mengandalkan elemen HTML asli agar bekerja tanpa ketergantungan skrip berat.

### Buttons
- **Shape:** Sudut membulat sedang (`8px`), `font-weight: 600`, bantalan standar `0.65rem 1.1rem` dan varian kecil `0.42rem 0.82rem`.
- **Primary:** Latar Zamrud Rimba (`#0b5d46`) dengan teks putih; saat hover berpindah ke Zamrud Rimba Gelap (`#073f2f`) dalam `180ms ease-out`.
- **Hover / Focus:** Ring fokus `2px solid #0b5d46` berjarak `2px` di luar kontrol; status nonaktif menurunkan opasitas ke `0.65`.
- **Secondary / Gold / Outline Light:** Tombol sekunder berlatar putih bertopi border `#9fb4a9` dan teks `#0b5d46`; tombol Emas Tenun (`#b8841e`) berteks `#12241c` khusus untuk ajakan bertindak utama di latar gelap; tombol bingkai terang berlatar transparan dengan border putih tipis untuk aksi sekunder di header maupun hero gelap.

### Chips
- **Style:** Kapsul penuh (`999px`), border `1px solid`, latar lembut semantik (`#e6f4ed`, `#fef5e1`, atau `#fcebe9`), diawali titik indikator lingkaran `8px` (`#12805c`, `#c48410`, atau `#c9372c`), serta pil info `#073f2f` di hero.
- **State:** Statis informatif tanpa efek berkedip; varian lencana piloting memakai Emas Tenun Lembut (`#f5edd6`) berborder `#d9b565` dengan teks `#634305`.

### Cards / Containers
- **Corner Style:** Radius kartu konsisten (`12px`).
- **Background:** Permukaan Kartu (`#ffffff`) pada grid beranda dan panel formulir, serta Zamrud Rimba Gelap (`#073f2f`) untuk panel ringkasan pantauan hero.
- **Shadow Strategy:** Nol bayangan (mengikuti *The Flat-By-Default Rule*).
- **Border:** Border seragam `1px solid #d5d2c4` (atau `1px solid #9bd0b5` pada catatan ekoteologi); kartu Aksi Cepat mengubah warna tepi menjadi `#0b5d46` dan naik `-2px` saat hover.
- **Internal Padding:** `1.2rem` hingga `1.25rem` pada kartu grid; `1.75rem` pada wadah formulir kerja.

### Inputs / Fields
- **Style:** Label berada di atas kolom input (jarak `0.35rem`, `font-weight: 600`, `0.9rem`), kotak input putih (`#ffffff`) berborder `1px solid #9fb4a9`, radius `8px`, padding `0.62rem 0.78rem`, dan warna kursor `#0b5d46`.
- **Focus:** Menggeser warna border menjadi `#0b5d46` sekaligus menampilkan outline `2px solid #0b5d46`.
- **Error / Disabled:** Umpan balik kesalahan atau status ditampilkan melalui blok peringatan datar (`1px solid` latar status lembut) di atas formulir sebelum pengiriman ulang, didukung validasi HTML5 bawaan (`required`, `minlength`, `pattern`).

### Navigation
- Header penuh berlatar `#073f2f` memuat lockup logo BERSATU (ikon 38x38px plus judul dan subjudul instansi), tombol toggle menu mobile berikon garis SVG `20px`, tautan navigasi berbasis peran (menyembunyikan *Lapor Dini* dari `superadmin`, `verifikator`, dan `pimpinan`), serta kendali sesi (*Masuk* / *Daftar Agen & Warga* atau lencana nama-peran beserta tombol *Keluar*). Mulai lebar `960px`, panel navigasi tampil sejajar horisontal. Pita peringatan *offline* kuning muncul otomatis di atas header saat sinyal terputus.

### Signature Component: Kartu Status Kondusivitas Wilayah
- Kartu beradius `12px` berlatar putih dengan kepala kartu berisi nama Kabupaten/Kota dan tag opsional *Lokus Piloting* (untuk Kabupaten Malinau), diikuti satu pil status netral bertitik `8px` (*Kondusif & Harmonis*, *Dalam Fasilitasi Dialog & Pembinaan*, atau *Dalam Koordinasi Intensif Lintas Sektor*), ringkasan deskriptif singkat, dan baris statistik kaki dengan garis pemisah tipis serta angka `tabular-nums` untuk jumlah kecamatan dan jumlah laporan valid.

## Do's and Don'ts

### Do:
- **Do** terapkan mode permukaan sesuai fungsi layar (`Persuade` di beranda publik, `Operate` di layar autentikasi/formulir kerja/admin, `Read` di materi edukasi & FAQ, `Experience` di galeri Media Hub).
- **Do** pakai pasangan nilai latar hangat (`#f6f5ef`), bidang sekunder (`#eceae0`), kartu putih (`#ffffff`), dan tinta pekat (`#12241c`) untuk menjaga kontras minimal WCAG 2.1 AA di bawah cahaya luar ruang.
- **Do** gunakan ikon SVG inline monoline berstroke `1.75` hingga `2` (`viewBox="0 0 24 24"`) dan titik indikator CSS lingkaran `8px`.
- **Do** terapkan `font-variant-numeric: tabular-nums` untuk seluruh angka wilayah, jumlah laporan valid, dan penanggalan agenda.
- **Do** pertahankan ring fokus `2px solid #0b5d46` dan aturan `@media (prefers-reduced-motion: reduce)` pada semua kontrol interaktif.

### Don't:
- **Don't** tambahkan `box-shadow` lebar pada kartu atau menggabungkan kartu berbingkai di dalam kartu lain (nested cards / ghost cards).
- **Don't** gunakan `border-left` atau `border-right` lebih tebal dari `1px` untuk membedakan status kartu maupun pesan peringatan.
- **Don't** gunakan emoji Unicode sebagai pengganti ikon UI maupun penomoran dekoratif atau eyebrow/kicker di atas elemen heading.
- **Don't** tampilkan label internal verifikator (seperti ancaman atau potensi konflik) maupun warna merah alarm blok penuh pada antarmuka publik.
- **Don't** muat pustaka CSS/JS eksternal, bundler front-end, atau berkas font eksternal yang dapat melanggar batas 1 MB per berkas di InfinityFree atau memperlambat pemuatan di wilayah 3TP.
