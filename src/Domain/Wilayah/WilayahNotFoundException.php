<?php

declare(strict_types=1);

namespace App\Domain\Wilayah;

use App\Domain\DomainException\DomainRecordNotFoundException;

class WilayahNotFoundException extends DomainRecordNotFoundException
{
    public $message = 'Data wilayah yang diminta tidak ditemukan.';
}
