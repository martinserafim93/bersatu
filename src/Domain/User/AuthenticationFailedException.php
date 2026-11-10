<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\DomainException\DomainException;

class AuthenticationFailedException extends DomainException
{
    public $message = 'Email atau kata sandi salah.';
}
