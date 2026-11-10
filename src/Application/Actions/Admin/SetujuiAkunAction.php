<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Application\Actions\Auth\AccountAction;
use App\Domain\User\StatusAkun;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;

final class SetujuiAkunAction extends AccountAction
{
    protected function handle(): Response
    {
        $id = (int) $this->resolveArg('id');

        $body = $this->request->getParsedBody();
        $role = null;
        if (is_array($body) && isset($body['role']) && is_string($body['role']) && trim($body['role']) !== '') {
            $role = trim($body['role']);
        }

        $this->accounts->approve($id, $role);

        /** @var User|null $actor */
        $actor = $this->request->getAttribute('user');
        $this->logger->info('Aksi akun', [
            'aksi' => 'setujui',
            'aktor_id' => $actor instanceof User ? $actor->getId() : null,
            'target_id' => $id,
        ]);

        return $this->respondWithData([
            'id' => $id,
            'status_akun' => StatusAkun::AKTIF,
        ]);
    }
}
