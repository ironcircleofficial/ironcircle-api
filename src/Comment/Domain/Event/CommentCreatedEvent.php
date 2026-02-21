<?php

declare(strict_types=1);

namespace App\Comment\Domain\Event;

use DateTimeImmutable;

final readonly class CommentCreatedEvent
{
    public function __construct(
        public string $commentId,
        public string $postId,
        public string $authorId,
        public ?string $parentCommentId,
        public DateTimeImmutable $createdAt
    ) {
    }
}
