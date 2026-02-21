<?php

declare(strict_types=1);

namespace App\Comment\Domain\Event;

use DateTimeImmutable;

final readonly class CommentUpdatedEvent
{
    public function __construct(
        public string $commentId,
        public string $postId,
        public DateTimeImmutable $updatedAt
    ) {
    }
}
