<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use Psr\Http\Message\ResponseInterface as Response;

final class MeAction extends AccountAction
{
    protected function handle(): Response
    {
        return $this->respondWithData([
            'user' => $this->request->getAttribute('user'),
        ]);
    }
}
