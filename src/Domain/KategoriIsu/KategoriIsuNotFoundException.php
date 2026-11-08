<?php

declare(strict_types=1);

namespace App\Domain\KategoriIsu;

use App\Domain\DomainException\DomainRecordNotFoundException;

class KategoriIsuNotFoundException extends DomainRecordNotFoundException
{
    public $message = 'Kategori isu yang diminta tidak ditemukan.';
}
