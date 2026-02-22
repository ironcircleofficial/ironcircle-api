<?php

declare(strict_types=1);

namespace App\Vote\Application\Command;

final readonly class CastVoteCommand
{
    public function __construct(
        public string $userId,
        public string $targetType,
        public string $targetId,
        public int $value
    ) {
    }
}
