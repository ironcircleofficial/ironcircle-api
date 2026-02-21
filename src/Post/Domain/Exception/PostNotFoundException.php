<?php

declare(strict_types=1);

namespace App\Post\Domain\Exception;

final class PostNotFoundException extends \RuntimeException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('Post with ID "%s" was not found', $id));
    }
}
