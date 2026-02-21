<?php

declare(strict_types=1);

namespace App\Comment\Domain\Exception;

final class UnauthorizedCommentAccessException extends \RuntimeException
{
    public static function notAuthor(): self
    {
        return new self('Only the comment author, a circle moderator, or an admin can perform this action');
    }

    public static function replyToReply(): self
    {
        return new self('Replies to replies are not allowed. Only top-level comments can be replied to');
    }
}
