<?php

declare(strict_types=1);

namespace App\Circle\Domain\Exception;

use DomainException;

final class CircleNotFoundException extends DomainException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('Circle with ID "%s" not found', $id));
    }

    public static function withSlug(string $slug): self
    {
        return new self(sprintf('Circle with slug "%s" not found', $slug));
    }
}
