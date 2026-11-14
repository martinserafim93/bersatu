<?php

declare(strict_types=1);

namespace App\Application\Actions\Home;

use App\Application\Actions\PageAction;
use Psr\Http\Message\ResponseInterface as Response;

final class OfflinePageAction extends PageAction
{
    protected function action(): Response
    {
        return $this->renderPage('home/offline.php', [
            'pageTitle' => 'Mode Offline - Portal BERSATU',
            'pageDescription' => 'Halaman fallback saat perangkat berada di wilayah tanpa jaringan internet.',
            'surfaceMode' => 'operate',
            'activePath' => '/offline',
        ]);
    }
}
