<?php

declare(strict_types=1);

namespace App\Auth\Domain\Exception;

use DomainException;

final class InvalidCredentialsException extends DomainException
{
    public static function create(): self
    {
        return new self('Invalid username or password');
    }
}
