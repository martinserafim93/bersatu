<?php

declare(strict_types=1);

namespace App\Domain\User;

final class StatusAkun
{
    public const MENUNGGU_VERIFIKASI = 'menunggu_verifikasi';
    public const AKTIF = 'aktif';
    public const DITOLAK = 'ditolak';
    public const NONAKTIF = 'nonaktif';

    public const ALL = [
        self::MENUNGGU_VERIFIKASI,
        self::AKTIF,
        self::DITOLAK,
        self::NONAKTIF,
    ];
}
