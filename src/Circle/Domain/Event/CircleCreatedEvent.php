<?php

declare(strict_types=1);

namespace App\Circle\Domain\Event;

use DateTimeImmutable;

final readonly class CircleCreatedEvent
{
    public function __construct(
        public string $circleId,
        public string $name,
        public string $slug,
        public string $creatorId,
        public DateTimeImmutable $occurredAt
    ) {
    }

    public static function create(
        string $circleId,
        string $name,
        string $slug,
        string $creatorId
    ): self {
        return new self(
            circleId: $circleId,
            name: $name,
            slug: $slug,
            creatorId: $creatorId,
            occurredAt: new DateTimeImmutable()
        );
    }
}
