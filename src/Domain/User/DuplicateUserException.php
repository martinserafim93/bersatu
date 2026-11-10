<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\DomainException\DomainException;

class DuplicateUserException extends DomainException
{
    public $message = 'Email atau NIP/NIK sudah terdaftar.';
}
