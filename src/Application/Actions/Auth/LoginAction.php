<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Application\Auth\AuthSession;
use Psr\Http\Message\ResponseInterface as Response;
use RuntimeException;

final class LoginAction extends AccountAction
{
    protected function handle(): Response
    {
        $body = $this->bodyArray();
        $user = $this->accounts->authenticate(
            $body['email'] ?? null,
            $body['password'] ?? null
        );

        $session = $this->request->getAttribute('session');
        if (!($session instanceof AuthSession)) {
            throw new RuntimeException('Sesi tidak tersedia.');
        }

        $session->login($user->getId());

        return $this->respondWithData(['user' => $user]);
    }
}
