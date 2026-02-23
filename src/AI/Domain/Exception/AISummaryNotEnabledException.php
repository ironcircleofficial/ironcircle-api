<?php

declare(strict_types=1);

namespace App\AI\Domain\Exception;

use RuntimeException;

final class AISummaryNotEnabledException extends RuntimeException
{
    public static function forPost(string $postId): self
    {
        return new self(sprintf('AI summary is not enabled for post %s', $postId));
    }
}
