<?php

declare(strict_types=1);

namespace App\Moderation\Application\Command;

final readonly class ResolveFlagCommand
{
    public function __construct(
        public string $flagId,
        public string $moderatorId
    ) {
    }
}
