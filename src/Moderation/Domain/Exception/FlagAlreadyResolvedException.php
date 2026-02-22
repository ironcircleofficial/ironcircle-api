<?php

declare(strict_types=1);

namespace App\Moderation\Domain\Exception;

final class FlagAlreadyResolvedException extends \RuntimeException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('Flag "%s" has already been resolved.', $id));
    }
}
