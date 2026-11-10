<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Domain\DomainException\DomainException;

class IdentityAccessDeniedException extends DomainException
{
    public $message = 'Anda tidak berhak melihat identitas ini.';
}
