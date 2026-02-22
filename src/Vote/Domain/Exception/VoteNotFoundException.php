<?php

declare(strict_types=1);

namespace App\Vote\Domain\Exception;

final class VoteNotFoundException extends \RuntimeException
{
    public static function forTarget(string $targetType, string $targetId): self
    {
        return new self(sprintf('Vote for %s with ID "%s" was not found', $targetType, $targetId));
    }
}
