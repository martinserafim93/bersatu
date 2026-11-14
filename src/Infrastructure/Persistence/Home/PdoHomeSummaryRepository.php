<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Home;

use App\Domain\Home\HomeSummaryRepository;
use App\Domain\Home\PublicCondusivityStatus;
use PDO;
use Psr\Container\ContainerInterface;
use Throwable;

final class PdoHomeSummaryRepository implements HomeSummaryRepository
{
    private bool $connectionResolved = false;

    private ?PDO $pdo = null;

    public function __construct(private ContainerInterface $container)
    {
    }

    public function getKabupatenCondusivitySummary(): array
    {
        $summary = PublicCondusivityStatus::buildDefaultKabupatenSummary();
        $pdo = $this->connection();
        if ($pdo === null) {
            return $summary;
        }

        $sql = "SELECT
                w.nama_kabupaten,
                COUNT(DISTINCT w.id) AS jumlah_kecamatan,
                COUNT(CASE
                    WHEN l.status_laporan IN ('diverifikasi', 'proses_mediasi', 'selesai') THEN l.id
                    ELSE NULL
                END) AS total_laporan_valid,
                COUNT(CASE
                    WHEN l.status_laporan IN ('diverifikasi', 'proses_mediasi')
                         AND l.status_kerawanan = 'merah' THEN l.id
                    ELSE NULL
                END) AS active_merah,
                COUNT(CASE
                    WHEN l.status_laporan IN ('diverifikasi', 'proses_mediasi')
                         AND l.status_kerawanan = 'kuning' THEN l.id
                    ELSE NULL
                END) AS active_kuning
            FROM wilayah w
            LEFT JOIN laporan_ews l ON l.wilayah_id = w.id
            GROUP BY w.nama_kabupaten";

        try {
            $stmt = $pdo->query($sql);
            if ($stmt === false) {
                return $summary;
            }

            /** @var array<string, array<string, mixed>> $byKabupaten */
            $byKabupaten = [];
            while ($row = $stmt->fetch()) {
                /** @var array<string, mixed> $row */
                $byKabupaten[(string) $row['nama_kabupaten']] = $row;
            }

            if ($byKabupaten === []) {
                return $summary;
            }

            foreach ($summary as $idx => $defaultRow) {
                $name = $defaultRow['nama_kabupaten'];
                if (!isset($byKabupaten[$name])) {
                    continue;
                }

                $dbRow = $byKabupaten[$name];
                $status = PublicCondusivityStatus::resolveKabupatenStatus(
                    (int) $dbRow['active_merah'],
                    (int) $dbRow['active_kuning']
                );

                $summary[$idx]['jumlah_kecamatan'] = (int) $dbRow['jumlah_kecamatan'];
                $summary[$idx]['status_kerawanan'] = $status;
                $summary[$idx]['label_publik'] = PublicCondusivityStatus::publicLabel($status);
                $summary[$idx]['deskripsi_publik'] = PublicCondusivityStatus::publicDescription($status);
                $summary[$idx]['total_laporan_valid'] = (int) $dbRow['total_laporan_valid'];
            }

            return $summary;
        } catch (Throwable) {
            return $summary;
        }
    }

    public function getUpcomingAgendaTicker(int $limit = 5): array
    {
        $safeLimit = max(1, min(20, $limit));
        $pdo = $this->connection();

        if ($pdo !== null) {
            $sql = "SELECT
                    a.id,
                    a.judul_agenda,
                    a.lokasi_tempat,
                    COALESCE(w.nama_kabupaten, 'Provinsi Kalimantan Utara') AS nama_kabupaten,
                    a.tanggal_mulai
                FROM agenda_kerukunan a
                LEFT JOIN wilayah w ON w.id = a.wilayah_id
                WHERE a.status_tampil = 1
                ORDER BY a.tanggal_mulai ASC, a.id ASC
                LIMIT {$safeLimit}";

            try {
                $stmt = $pdo->query($sql);
                if ($stmt !== false) {
                    $items = [];
                    while ($row = $stmt->fetch()) {
                        /** @var array<string, mixed> $row */
                        $items[] = [
                            'id' => (int) $row['id'],
                            'judul_agenda' => (string) $row['judul_agenda'],
                            'lokasi_tempat' => (string) $row['lokasi_tempat'],
                            'nama_kabupaten' => (string) $row['nama_kabupaten'],
                            'tanggal_mulai' => (string) $row['tanggal_mulai'],
                        ];
                    }

                    if ($items !== []) {
                        return $items;
                    }
                }
            } catch (Throwable) {
                return $this->defaultAgendaTicker();
            }
        }

        return $this->defaultAgendaTicker();
    }

    public function getMediaHubHighlights(int $limit = 4): array
    {
        $safeLimit = max(1, min(20, $limit));
        $pdo = $this->connection();

        if ($pdo !== null) {
            $sql = "SELECT
                    id,
                    judul,
                    tipe_konten,
                    agama,
                    tokoh_narasumber
                FROM media_hub
                WHERE status_tampil = 1
                ORDER BY published_at DESC, id DESC
                LIMIT {$safeLimit}";

            try {
                $stmt = $pdo->query($sql);
                if ($stmt !== false) {
                    $items = [];
                    while ($row = $stmt->fetch()) {
                        /** @var array<string, mixed> $row */
                        $items[] = [
                            'id' => (int) $row['id'],
                            'judul' => (string) $row['judul'],
                            'tipe_konten' => (string) $row['tipe_konten'],
                            'agama' => (string) $row['agama'],
                            'tokoh_narasumber' => (string) $row['tokoh_narasumber'],
                        ];
                    }

                    if ($items !== []) {
                        return $items;
                    }
                }
            } catch (Throwable) {
                return $this->defaultMediaHighlights();
            }
        }

        return $this->defaultMediaHighlights();
    }

    private function connection(): ?PDO
    {
        if ($this->connectionResolved) {
            return $this->pdo;
        }

        $this->connectionResolved = true;

        try {
            $pdo = $this->container->get(PDO::class);
            $this->pdo = $pdo instanceof PDO ? $pdo : null;
        } catch (Throwable) {
            $this->pdo = null;
        }

        return $this->pdo;
    }

    /**
     * @return array<int, array{
     *     id: int,
     *     judul_agenda: string,
     *     lokasi_tempat: string,
     *     nama_kabupaten: string,
     *     tanggal_mulai: string
     * }>
     */
    private function defaultAgendaTicker(): array
    {
        return [
            [
                'id' => 1,
                'judul_agenda' => 'Dialog Lintas Iman & Penguatan Ekoteologi Kabupaten Malinau',
                'lokasi_tempat' => 'Sekretariat FKUB Kabupaten Malinau',
                'nama_kabupaten' => 'Kabupaten Malinau',
                'tanggal_mulai' => '2026-05-20 09:00:00',
            ],
            [
                'id' => 2,
                'judul_agenda' => 'Bimtek Agen Kerukunan Digital Wilayah 3TP & Perbatasan',
                'lokasi_tempat' => 'Aula Kanwil Kemenag Kalimantan Utara, Tanjung Selor',
                'nama_kabupaten' => 'Kabupaten Bulungan',
                'tanggal_mulai' => '2026-05-27 08:30:00',
            ],
            [
                'id' => 3,
                'judul_agenda' => 'Forum Silaturahmi Penyuluh Agama, Kepala KUA & Tokoh Adat Perbatasan',
                'lokasi_tempat' => 'Kankemenag Kabupaten Nunukan',
                'nama_kabupaten' => 'Kabupaten Nunukan',
                'tanggal_mulai' => '2026-06-03 10:00:00',
            ],
        ];
    }

    /**
     * @return array<int, array{
     *     id: int,
     *     judul: string,
     *     tipe_konten: string,
     *     agama: string,
     *     tokoh_narasumber: string
     * }>
     */
    private function defaultMediaHighlights(): array
    {
        return [
            [
                'id' => 1,
                'judul' => 'Suara Harmoni Perbatasan: Merawat Bumi, Merawat Kerukunan',
                'tipe_konten' => 'podcast',
                'agama' => 'semua_agama',
                'tokoh_narasumber' => 'Tim Penyuluh Lintas Agama Kanwil Kemenag Kaltara',
            ],
            [
                'id' => 2,
                'judul' => 'Memahami PBM No. 9 & No. 8 Tahun 2006 Secara Utuh',
                'tipe_konten' => 'infografis',
                'agama' => 'semua_agama',
                'tokoh_narasumber' => 'Forum Kerukunan Umat Beragama (FKUB) Kaltara',
            ],
            [
                'id' => 3,
                'judul' => 'Ekoteologi Lintas Iman: Menjaga Hutan & Sungai Malinau',
                'tipe_konten' => 'naskah_khotbah',
                'agama' => 'semua_agama',
                'tokoh_narasumber' => 'Pokja Penyuluh Agama (IPARI) Kab. Malinau',
            ],
        ];
    }
}
