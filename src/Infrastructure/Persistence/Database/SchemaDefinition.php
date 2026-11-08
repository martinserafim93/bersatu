<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Database;

class SchemaDefinition
{
    /**
     * @var array<int, string>
     */
    public const TABLE_ORDER = [
        'wilayah',
        'users',
        'kategori_isu',
        'laporan_ews',
        'bukti_laporan',
        'tindak_lanjut',
        'kategori_media',
        'media_hub',
        'faq_chatbot',
        'rumah_ibadah',
        'agenda_kerukunan',
        'kontak_fkub',
    ];

    /**
     * @return array<string, string>
     */
    public static function getCreateTableStatements(): array
    {
        return [
            'wilayah' => <<<SQL
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
SQL,
            'users' => <<<SQL
CREATE TABLE IF NOT EXISTS users (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    nip_nik_encrypted TEXT NOT NULL,
    nip_nik_hash CHAR(64) NOT NULL UNIQUE,
    nama_lengkap VARCHAR(150) NOT NULL,
    instansi VARCHAR(150) NULL,
    no_wa_encrypted TEXT NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM(
        'superadmin', 'pimpinan', 'verifikator', 'penyuluh_ipari', 'kua', 'fkub', 'masyarakat'
    ) NOT NULL DEFAULT 'masyarakat',
    wilayah_id INT NOT NULL,
    status_akun ENUM('menunggu_verifikasi', 'aktif', 'ditolak', 'nonaktif') NOT NULL DEFAULT 'menunggu_verifikasi',
    status_aktif BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_wilayah FOREIGN KEY (wilayah_id) REFERENCES wilayah(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL,
            'kategori_isu' => <<<SQL
CREATE TABLE IF NOT EXISTS kategori_isu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    deskripsi TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL,
            'laporan_ews' => <<<SQL
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
    status_laporan ENUM(
        'draft', 'terkirim', 'diverifikasi', 'proses_mediasi', 'selesai', 'ditolak_hoaks'
    ) NOT NULL DEFAULT 'terkirim',
    alert_terkirim_at DATETIME NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_laporan_wilayah_status (wilayah_id, status_laporan, status_kerawanan),
    CONSTRAINT fk_laporan_pelapor FOREIGN KEY (pelapor_id) REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_laporan_wilayah FOREIGN KEY (wilayah_id) REFERENCES wilayah(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_laporan_kategori FOREIGN KEY (kategori_id)
        REFERENCES kategori_isu(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL,
            'bukti_laporan' => <<<SQL
CREATE TABLE IF NOT EXISTS bukti_laporan (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    laporan_id BIGINT NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    tipe_file VARCHAR(50) NOT NULL,
    caption VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_bukti_laporan FOREIGN KEY (laporan_id) REFERENCES laporan_ews(id) ON UPDATE CASCADE ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL,
            'tindak_lanjut' => <<<SQL
CREATE TABLE IF NOT EXISTS tindak_lanjut (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    laporan_id BIGINT NOT NULL,
    petugas_id BIGINT NOT NULL,
    catatan_analisis TEXT NOT NULL,
    rekomendasi_mitigasi TEXT NOT NULL,
    perubahan_status_kerawanan ENUM('hijau', 'kuning', 'merah') NOT NULL,
    tanggal_penanganan DATETIME NOT NULL,
    CONSTRAINT fk_tindak_lanjut_laporan FOREIGN KEY (laporan_id)
        REFERENCES laporan_ews(id) ON UPDATE CASCADE ON DELETE CASCADE,
    CONSTRAINT fk_tindak_lanjut_petugas FOREIGN KEY (petugas_id)
        REFERENCES users(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL,
            'kategori_media' => <<<SQL
CREATE TABLE IF NOT EXISTS kategori_media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_kategori VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL,
            'media_hub' => <<<SQL
CREATE TABLE IF NOT EXISTS media_hub (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kategori_id INT NULL,
    agama ENUM(
        'semua_agama', 'islam', 'kristen', 'katolik', 'hindu', 'buddha', 'khonghucu'
    ) NOT NULL DEFAULT 'semua_agama',
    judul VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    tipe_konten ENUM('video', 'animasi', 'infografis', 'podcast', 'naskah_khotbah') NOT NULL,
    url_file VARCHAR(255) NOT NULL,
    thumbnail_url VARCHAR(255) NULL,
    deskripsi TEXT NOT NULL,
    tokoh_narasumber VARCHAR(150) NOT NULL,
    status_tampil BOOLEAN NOT NULL DEFAULT TRUE,
    published_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_media_kategori FOREIGN KEY (kategori_id)
        REFERENCES kategori_media(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL,
            'faq_chatbot' => <<<SQL
CREATE TABLE IF NOT EXISTS faq_chatbot (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pertanyaan_kunci VARCHAR(255) NOT NULL,
    kata_kunci VARCHAR(255) NOT NULL,
    jawaban TEXT NOT NULL,
    kategori ENUM('pbm_2006', 'moderasi', 'ekoteologi', 'perbatasan') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL,
            'rumah_ibadah' => <<<SQL
CREATE TABLE IF NOT EXISTS rumah_ibadah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_rumah_ibadah VARCHAR(150) NOT NULL,
    agama ENUM('islam', 'kristen', 'katolik', 'hindu', 'buddha', 'khonghucu') NOT NULL,
    wilayah_id INT NOT NULL,
    alamat TEXT NOT NULL,
    latitude DECIMAL(10,8) NULL,
    longitude DECIMAL(11,8) NULL,
    status_verifikasi BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT fk_rumah_ibadah_wilayah FOREIGN KEY (wilayah_id)
        REFERENCES wilayah(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL,
            'agenda_kerukunan' => <<<SQL
CREATE TABLE IF NOT EXISTS agenda_kerukunan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    judul_agenda VARCHAR(200) NOT NULL,
    deskripsi TEXT NOT NULL,
    wilayah_id INT NULL,
    lokasi_tempat VARCHAR(200) NOT NULL,
    tanggal_mulai DATETIME NOT NULL,
    tanggal_selesai DATETIME NULL,
    status_tampil BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT fk_agenda_wilayah FOREIGN KEY (wilayah_id)
        REFERENCES wilayah(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL,
            'kontak_fkub' => <<<SQL
CREATE TABLE IF NOT EXISTS kontak_fkub (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_sekretariat VARCHAR(150) NOT NULL,
    wilayah_id INT NULL,
    alamat TEXT NOT NULL,
    no_wa VARCHAR(20) NOT NULL,
    email VARCHAR(100) NULL,
    status_aktif BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT fk_kontak_fkub_wilayah FOREIGN KEY (wilayah_id)
        REFERENCES wilayah(id) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
SQL,
        ];
    }
}
