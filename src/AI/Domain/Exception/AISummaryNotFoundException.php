<?php

declare(strict_types=1);

namespace App\AI\Domain\Exception;

use RuntimeException;

final class AISummaryNotFoundException extends RuntimeException
{
    public static function forPost(string $postId): self
    {
        return new self(sprintf('AI summary for post %s was not found', $postId));
    }
}
