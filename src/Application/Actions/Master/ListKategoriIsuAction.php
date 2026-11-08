<?php

declare(strict_types=1);

namespace App\Application\Actions\Master;

use App\Application\Actions\Action;
use App\Domain\KategoriIsu\KategoriIsuRepository;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

class ListKategoriIsuAction extends Action
{
    public function __construct(
        LoggerInterface $logger,
        private KategoriIsuRepository $kategoriIsuRepository
    ) {
        parent::__construct($logger);
    }

    protected function action(): Response
    {
        $items = $this->kategoriIsuRepository->findAll();

        return $this->respondWithData($items);
    }
}
