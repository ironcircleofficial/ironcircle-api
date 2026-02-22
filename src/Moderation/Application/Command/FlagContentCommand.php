<?php

declare(strict_types=1);

namespace App\Moderation\Application\Command;

final readonly class FlagContentCommand
{
    public function __construct(
        public string $reporterId,
        public string $targetType,
        public string $targetId,
        public string $reason
    ) {
    }
}
