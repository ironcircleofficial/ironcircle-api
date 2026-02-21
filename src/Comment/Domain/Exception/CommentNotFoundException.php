<?php

declare(strict_types=1);

namespace App\Comment\Domain\Exception;

final class CommentNotFoundException extends \RuntimeException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('Comment with ID "%s" was not found', $id));
    }
}
