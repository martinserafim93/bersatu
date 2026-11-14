<?php

declare(strict_types=1);

namespace App\Application\Actions\Home;

use App\Application\Actions\Action;
use App\Domain\Home\HomeSummaryRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

final class BerandaSummaryApiAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private HomeSummaryRepository $summaryRepository
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $kabupatenSummary = $this->summaryRepository->getKabupatenCondusivitySummary();
        $totalKecamatan = array_sum(array_map(
            static fn (array $item): int => (int) $item['jumlah_kecamatan'],
            $kabupatenSummary
        ));

        return $this->respondWithData([
            'provinsi' => 'Kalimantan Utara',
            'peringkat_ikub_nasional' => 1,
            'tahun_ikub' => 2025,
            'total_kabupaten_kota' => count($kabupatenSummary),
            'total_kecamatan' => $totalKecamatan,
            'lokus_piloting' => 'Kabupaten Malinau',
            'kabupaten_summary' => $kabupatenSummary,
            'agenda_ticker' => $this->summaryRepository->getUpcomingAgendaTicker(5),
            'media_highlights' => $this->summaryRepository->getMediaHubHighlights(4),
        ]);
    }
}
