<?php

declare(strict_types=1);

namespace App\Application\Actions\Auth;

use App\Domain\User\StatusAkun;
use Psr\Http\Message\ResponseInterface as Response;

final class RegisterAction extends AccountAction
{
    protected function handle(): Response
    {
        $id = $this->accounts->register($this->bodyArray());

        return $this->respondWithData([
            'id' => $id,
            'status_akun' => StatusAkun::MENUNGGU_VERIFIKASI,
            'pesan' => 'Pendaftaran diterima. Akun akan aktif setelah diverifikasi superadmin.',
        ], 201);
    }
}
