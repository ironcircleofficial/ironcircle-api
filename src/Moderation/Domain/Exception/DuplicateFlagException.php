<?php

declare(strict_types=1);

namespace App\Moderation\Domain\Exception;

final class DuplicateFlagException extends \RuntimeException
{
    public static function forTarget(string $targetType, string $targetId): self
    {
        return new self(sprintf('You have already flagged this %s (ID: %s).', $targetType, $targetId));
    }
}
