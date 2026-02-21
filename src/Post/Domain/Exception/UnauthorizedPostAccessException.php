<?php

declare(strict_types=1);

namespace App\Post\Domain\Exception;

final class UnauthorizedPostAccessException extends \RuntimeException
{
    public static function notAuthor(): self
    {
        return new self('Only the post author or a circle moderator can perform this action');
    }
}
