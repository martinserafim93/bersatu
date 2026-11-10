<?php

declare(strict_types=1);

namespace App\Application\Actions\Admin;

use App\Application\Actions\Auth\AccountAction;
use App\Domain\User\User;
use Psr\Http\Message\ResponseInterface as Response;

final class CreateAkunAction extends AccountAction
{
    protected function handle(): Response
    {
        $id = $this->accounts->createInternal($this->bodyArray());

        /** @var User|null $actor */
        $actor = $this->request->getAttribute('user');
        $this->logger->info('Aksi akun', [
            'aksi' => 'buat_internal',
            'aktor_id' => $actor instanceof User ? $actor->getId() : null,
            'target_id' => $id,
        ]);

        return $this->respondWithData(['id' => $id], 201);
    }
}
