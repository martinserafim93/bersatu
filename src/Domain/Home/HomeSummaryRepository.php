<?php

declare(strict_types=1);

namespace App\Domain\Home;

interface HomeSummaryRepository
{
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
    public function getKabupatenCondusivitySummary(): array;

    /**
     * @param int $limit
     * @return array<int, array{
     *     id: int,
     *     judul_agenda: string,
     *     lokasi_tempat: string,
     *     nama_kabupaten: string,
     *     tanggal_mulai: string
     * }>
     */
    public function getUpcomingAgendaTicker(int $limit = 5): array;

    /**
     * @param int $limit
     * @return array<int, array{
     *     id: int,
     *     judul: string,
     *     tipe_konten: string,
     *     agama: string,
     *     tokoh_narasumber: string
     * }>
     */
    public function getMediaHubHighlights(int $limit = 4): array;
}
