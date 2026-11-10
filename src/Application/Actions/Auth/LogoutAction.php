<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Auth\AuthSession;
use Psr\Http\Message\ResponseInterface as Response;

final class LogoutAction extends AccountAction
{
    protected function handle(): Response
    {
        $session = $this->request->getAttribute('session');
        if ($session instanceof AuthSession) {
            $session->logout();
        }

        return $this->respondWithData(['pesan' => 'Anda telah keluar.']);
    }
}
