<?php

declare(strict_types=1);

namespace App\AI\Domain\Exception;

use RuntimeException;

final class AISummaryGenerationException extends RuntimeException
{
    public static function failed(string $reason): self
    {
        return new self(sprintf('Failed to generate AI summary: %s', $reason));
    }
}
