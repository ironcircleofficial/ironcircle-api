<?php

declare(strict_types=1);

namespace App\User\Domain\Exception;

final class UserNotFoundException extends \RuntimeException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('User with ID "%s" was not found', $id));
    }
}
