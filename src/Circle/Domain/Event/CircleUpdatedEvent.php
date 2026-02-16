<?php

declare(strict_types=1);

namespace App\Circle\Domain\Event;

use DateTimeImmutable;

final readonly class CircleUpdatedEvent
{
    public function __construct(
        public string $circleId,
        public string $updatedBy,
        public DateTimeImmutable $occurredAt
    ) {
    }

    public static function create(string $circleId, string $updatedBy): self
    {
        return new self(
            circleId: $circleId,
            updatedBy: $updatedBy,
            occurredAt: new DateTimeImmutable()
        );
    }
}
