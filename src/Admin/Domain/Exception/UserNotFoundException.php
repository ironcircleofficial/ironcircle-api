<?php

declare(strict_types=1);

namespace App\Admin\Domain\Exception;

use DomainException;

final class UserNotFoundException extends DomainException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('User with ID "%s" not found', $id));
    }
}
