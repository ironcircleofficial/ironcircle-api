<?php

declare(strict_types=1);

namespace App\Moderation\Domain\Event;

final readonly class FlagCreatedEvent
{
    public function __construct(
        public string $flagId,
        public string $targetType,
        public string $targetId,
        public string $reporterId,
        public string $reason
    ) {
    }
}
