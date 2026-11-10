<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Infrastructure\Security\IdentityCipher;

class ReporterIdentityPolicy
{
    public function __construct(private IdentityCipher $cipher)
    {
    }

    public function canViewRealIdentity(User $viewer, User $owner): bool
    {
        if ($viewer->getStatusAkun() !== StatusAkun::AKTIF) {
            return false;
        }

        return $viewer->getRole() === Role::VERIFIKATOR || $viewer->getId() === $owner->getId();
    }

    /**
     * @return array{nip_nik: string, no_wa: string}
     * @throws IdentityAccessDeniedException
     */
    public function reveal(User $viewer, User $owner): array
    {
        if (!$this->canViewRealIdentity($viewer, $owner)) {
            throw new IdentityAccessDeniedException();
        }

        return [
            'nip_nik' => $this->cipher->decrypt($owner->getNipNikEncrypted()),
            'no_wa' => $this->cipher->decrypt($owner->getNoWaEncrypted()),
        ];
    }
}
