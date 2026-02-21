<?php

declare(strict_types=1);

namespace App\Post\Domain\Event;

use DateTimeImmutable;

final readonly class PostCreatedEvent
{
    public function __construct(
        public string $postId,
        public string $circleId,
        public string $authorId,
        public DateTimeImmutable $createdAt
    ) {
    }
}
