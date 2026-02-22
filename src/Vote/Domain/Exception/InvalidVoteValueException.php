<?php

declare(strict_types=1);

namespace App\Vote\Domain\Exception;

final class InvalidVoteValueException extends \DomainException
{
    public static function withValue(int $value): self
    {
        return new self(sprintf('Vote value "%d" is invalid. Must be +1 or -1', $value));
    }
}
