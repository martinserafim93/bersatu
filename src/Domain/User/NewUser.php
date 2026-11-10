<?php

declare(strict_types=1);

namespace App\Domain\User;

final class NewUser
{
    public function __construct(
        public readonly string $nipNikEncrypted,
        public readonly string $nipNikHash,
        public readonly string $namaLengkap,
        public readonly ?string $instansi,
        public readonly string $noWaEncrypted,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly string $role,
        public readonly int $wilayahId,
        public readonly string $statusAkun
    ) {
    }
}
