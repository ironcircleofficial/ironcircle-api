<?php

declare(strict_types=1);

namespace App\Moderation\Application\Command;

final readonly class DismissFlagCommand
{
    public function __construct(
        public string $flagId,
        public string $moderatorId
    ) {
    }
}
