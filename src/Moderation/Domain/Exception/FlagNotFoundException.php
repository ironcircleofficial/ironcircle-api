<?php

declare(strict_types=1);

namespace App\Moderation\Domain\Exception;

final class FlagNotFoundException extends \RuntimeException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('Flag with ID "%s" was not found.', $id));
    }
}
