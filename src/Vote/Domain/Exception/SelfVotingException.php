<?php

declare(strict_types=1);

namespace App\Vote\Domain\Exception;

final class SelfVotingException extends \DomainException
{
    public static function create(): self
    {
        return new self('You cannot vote on your own content');
    }
}
