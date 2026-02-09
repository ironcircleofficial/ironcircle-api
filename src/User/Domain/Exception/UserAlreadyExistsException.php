<?php

declare(strict_types=1);

namespace App\User\Domain\Exception;

use DomainException;

final class UserAlreadyExistsException extends DomainException
{
    public static function withUsername(string $username): self
    {
        return new self(sprintf('User with username "%s" already exists', $username));
    }

    public static function withEmail(string $email): self
    {
        return new self(sprintf('User with email "%s" already exists', $email));
    }
}
