<?php

declare(strict_types=1);

namespace App\Domain\User;

final class Role
{
    public const SUPERADMIN = 'superadmin';
    public const PIMPINAN = 'pimpinan';
    public const VERIFIKATOR = 'verifikator';
    public const PENYULUH_IPARI = 'penyuluh_ipari';
    public const KUA = 'kua';
    public const FKUB = 'fkub';
    public const MASYARAKAT = 'masyarakat';

    public const PUBLIC_REGISTRATION = [
        self::PENYULUH_IPARI,
        self::KUA,
        self::FKUB,
        self::MASYARAKAT,
    ];

    public const FIELD_REPORTERS = [
        self::PENYULUH_IPARI,
        self::KUA,
        self::FKUB,
    ];

    public const ADMIN_CREATABLE = [
        self::VERIFIKATOR,
        self::PIMPINAN,
        self::PENYULUH_IPARI,
        self::KUA,
        self::FKUB,
        self::MASYARAKAT,
    ];

    public const ALL = [
        self::SUPERADMIN,
        self::PIMPINAN,
        self::VERIFIKATOR,
        self::PENYULUH_IPARI,
        self::KUA,
        self::FKUB,
        self::MASYARAKAT,
    ];
}
