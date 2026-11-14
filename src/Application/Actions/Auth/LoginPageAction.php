<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Actions\PageAction;
use Psr\Http\Message\ResponseInterface as Response;

final class LoginPageAction extends PageAction
{
    protected function action(): Response
    {
        return $this->renderPage('auth/login.php', [
            'pageTitle' => 'Masuk Akun - Portal BERSATU',
            'pageDescription' => 'Halaman masuk akun Portal BERSATU untuk pelapor lapangan, '
                . 'masyarakat terverifikasi, verifikator, pimpinan, dan pengelola.',
            'surfaceMode' => 'operate',
            'activePath' => '/auth/login',
            'pageScripts' => ['/assets/js/auth.js'],
        ]);
    }
}
