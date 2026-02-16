<?php

declare(strict_types=1);

namespace App\Circle\Domain\Exception;

use DomainException;

final class CircleAlreadyExistsException extends DomainException
{
    public static function withSlug(string $slug): self
    {
        return new self(sprintf('Circle with slug "%s" already exists', $slug));
    }

    public static function withName(string $name): self
    {
        return new self(sprintf('Circle with name "%s" already exists', $name));
    }
}
