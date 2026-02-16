<?php

declare(strict_types=1);

namespace App\Circle\Domain\Exception;

use DomainException;

final class UnauthorizedCircleAccessException extends DomainException
{
    public static function create(): self
    {
        return new self('You are not authorized to access this circle');
    }

    public static function notModerator(): self
    {
        return new self('You must be a moderator to perform this action');
    }

    public static function notCreator(): self
    {
        return new self('Only the circle creator can perform this action');
    }
}
