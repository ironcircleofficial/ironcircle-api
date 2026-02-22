<?php

declare(strict_types=1);

namespace App\Vote\Application\Command;

final readonly class RetractVoteCommand
{
    public function __construct(
        public string $userId,
        public string $targetType,
        public string $targetId
    ) {
    }
}
