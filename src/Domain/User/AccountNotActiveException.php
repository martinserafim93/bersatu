<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\DomainException\DomainException;

class AccountNotActiveException extends DomainException
{
    public function __construct(private string $statusAkun)
    {
        $message = match ($statusAkun) {
            StatusAkun::MENUNGGU_VERIFIKASI => 'Akun Anda masih menunggu verifikasi superadmin.',
            StatusAkun::DITOLAK => 'Pendaftaran akun Anda ditolak. Hubungi pengelola portal.',
            StatusAkun::NONAKTIF => 'Akun Anda dinonaktifkan. Hubungi pengelola portal.',
            default => 'Akun Anda tidak aktif.',
        };

        parent::__construct($message);
    }

    public function getStatusAkun(): string
    {
        return $this->statusAkun;
    }
}
