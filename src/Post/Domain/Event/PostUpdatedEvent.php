<?php

declare(strict_types=1);

namespace App\Post\Domain\Event;

use DateTimeImmutable;

final readonly class PostUpdatedEvent
{
    public function __construct(
        public string $postId,
        public DateTimeImmutable $updatedAt
    ) {
    }
}
