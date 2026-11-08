<?php

declare(strict_types=1);

namespace App\Application\Actions\Master;

use App\Application\Actions\Action;
use App\Domain\Wilayah\Wilayah;
use App\Domain\Wilayah\WilayahRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class ListWilayahAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private WilayahRepository $wilayahRepository
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $queryParams = $this->request->getQueryParams();
        $kabupaten = trim((string) ($queryParams['kabupaten'] ?? ''));
        $includeGeojson = in_array($queryParams['include_geojson'] ?? '', ['1', 'true'], true);

        if ($kabupaten !== '') {
            $wilayahList = $this->wilayahRepository->findByKabupaten($kabupaten, $includeGeojson);
        } else {
            $wilayahList = $this->wilayahRepository->findAll($includeGeojson);
        }

        $items = array_map(function (Wilayah $wilayah) use ($includeGeojson) {
            return $includeGeojson ? $wilayah->jsonSerialize() : $wilayah->toSummaryArray();
        }, $wilayahList);

        $payload = [
            'total' => count($items),
            'kabupaten_list' => $this->wilayahRepository->listKabupaten(),
            'items' => $items,
        ];

        return $this->respondWithData($payload);
    }
}
