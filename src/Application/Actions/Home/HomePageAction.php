<?php

declare(strict_types=1);

namespace App\Application\Actions\Home;

use App\Application\Actions\PageAction;
use App\Application\Views\PhpRenderer;
use App\Domain\Home\HomeSummaryRepository;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Log\LoggerInterface;

final class HomePageAction extends PageAction
{
    public function __construct(
        LoggerInterface $logger,
        PhpRenderer $renderer,
        ContainerInterface $container,
        private HomeSummaryRepository $summaryRepository
    ) {
        parent::__construct($logger, $renderer, $container);
    }

    protected function action(): Response
    {
        return $this->renderPage('home/index.php', [
            'pageTitle' => 'Portal BERSATU - EWS & Promosi Kerukunan Umat Beragama Kaltara',
            'pageDescription' => 'Portal resmi Early Warning System (EWS) kerukunan umat beragama dan '
                . 'media moderasi Kanwil Kementerian Agama Provinsi Kalimantan Utara.',
            'surfaceMode' => 'persuade',
            'activePath' => '/',
            'kabupatenSummary' => $this->summaryRepository->getKabupatenCondusivitySummary(),
            'agendaTicker' => $this->summaryRepository->getUpcomingAgendaTicker(5),
            'mediaHighlights' => $this->summaryRepository->getMediaHubHighlights(4),
        ]);
    }
}
