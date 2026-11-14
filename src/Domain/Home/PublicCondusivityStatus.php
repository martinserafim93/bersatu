<?php

declare(strict_types=1);

namespace App\Domain\Home;

final class PublicCondusivityStatus
{
    public const HIJAU = 'hijau';
    public const KUNING = 'kuning';
    public const MERAH = 'merah';

    public const ALL_LEVELS = [
        self::HIJAU,
        self::KUNING,
        self::MERAH,
    ];

    /**
     * Hanya laporan terverifikasi valid yang boleh dihitung untuk publik.
     */
    public const VALID_PUBLIC_REPORT_STATUSES = [
        'diverifikasi',
        'proses_mediasi',
        'selesai',
    ];

    /**
     * Laporan selesai tidak lagi menaikkan tingkat kerawanan kabupaten saat ini.
     */
    public const ACTIVE_RISK_REPORT_STATUSES = [
        'diverifikasi',
        'proses_mediasi',
    ];

    /**
     * Cakupan baku 5 Kabupaten/Kota & 55 Kecamatan se-Kalimantan Utara.
     *
     * @var array<int, array{nama_kabupaten: string, jumlah_kecamatan: int, is_piloting: bool}>
     */
    public const DEFAULT_KABUPATEN_LIST = [
        [
            'nama_kabupaten' => 'Kabupaten Malinau',
            'jumlah_kecamatan' => 15,
            'is_piloting' => true,
        ],
        [
            'nama_kabupaten' => 'Kabupaten Bulungan',
            'jumlah_kecamatan' => 10,
            'is_piloting' => false,
        ],
        [
            'nama_kabupaten' => 'Kota Tarakan',
            'jumlah_kecamatan' => 4,
            'is_piloting' => false,
        ],
        [
            'nama_kabupaten' => 'Kabupaten Nunukan',
            'jumlah_kecamatan' => 21,
            'is_piloting' => false,
        ],
        [
            'nama_kabupaten' => 'Kabupaten Tana Tidung',
            'jumlah_kecamatan' => 5,
            'is_piloting' => false,
        ],
    ];

    public static function normalizeLevel(string $statusKerawanan): string
    {
        return in_array($statusKerawanan, self::ALL_LEVELS, true)
            ? $statusKerawanan
            : self::HIJAU;
    }

    public static function publicLabel(string $statusKerawanan): string
    {
        return match ($statusKerawanan) {
            self::MERAH => 'Dalam Koordinasi Intensif Lintas Sektor',
            self::KUNING => 'Dalam Fasilitasi Dialog & Pembinaan',
            default => 'Kondusif & Harmonis',
        };
    }

    public static function publicDescription(string $statusKerawanan): string
    {
        return match ($statusKerawanan) {
            self::MERAH => 'Tim Kanwil Kemenag, FKUB, dan unsur lintas sektor sedang melaksanakan '
                . 'koordinasi intensif penyelesaian di lapangan.',
            self::KUNING => 'Pembinaan, pendampingan dialog tokoh lintas agama, dan fasilitasi '
                . 'kerukunan sedang berjalan.',
            default => 'Situasi kerukunan umat beragama terjaga kondusif dan harmonis melalui '
                . 'sinergi tokoh lintas agama, adat, dan masyarakat.',
        };
    }

    public static function resolveKabupatenStatus(int $activeMerahCount, int $activeKuningCount): string
    {
        if ($activeMerahCount > 0) {
            return self::MERAH;
        }

        if ($activeKuningCount > 0) {
            return self::KUNING;
        }

        return self::HIJAU;
    }

    /**
     * @return array<int, array{
     *     nama_kabupaten: string,
     *     jumlah_kecamatan: int,
     *     is_piloting: bool,
     *     status_kerawanan: string,
     *     label_publik: string,
     *     deskripsi_publik: string,
     *     total_laporan_valid: int
     * }>
     */
    public static function buildDefaultKabupatenSummary(): array
    {
        $items = [];
        foreach (self::DEFAULT_KABUPATEN_LIST as $kab) {
            $status = self::HIJAU;
            $items[] = [
                'nama_kabupaten' => $kab['nama_kabupaten'],
                'jumlah_kecamatan' => $kab['jumlah_kecamatan'],
                'is_piloting' => $kab['is_piloting'],
                'status_kerawanan' => $status,
                'label_publik' => self::publicLabel($status),
                'deskripsi_publik' => self::publicDescription($status),
                'total_laporan_valid' => 0,
            ];
        }

        return $items;
    }
}
