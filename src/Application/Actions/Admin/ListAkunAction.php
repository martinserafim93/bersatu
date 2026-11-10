<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Application\Actions\Auth\AccountAction;
use Psr\Http\Message\ResponseInterface as Response;

final class ListAkunAction extends AccountAction
{
    protected function handle(): Response
    {
        $queryParams = $this->request->getQueryParams();
        $rawStatus = $queryParams['status'] ?? null;
        $status = is_string($rawStatus) && trim($rawStatus) !== ''
            ? trim($rawStatus)
            : null;

        $accounts = $this->accounts->listAccounts($status);

        return $this->respondWithData(['akun' => $accounts]);
    }
}
