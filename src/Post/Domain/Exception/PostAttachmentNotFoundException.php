<?php

declare(strict_types=1);

namespace App\Post\Domain\Exception;

final class PostAttachmentNotFoundException extends \RuntimeException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('Post attachment with ID "%s" was not found', $id));
    }
}
