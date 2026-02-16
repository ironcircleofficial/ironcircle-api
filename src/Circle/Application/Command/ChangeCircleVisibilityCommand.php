<?php

declare(strict_types=1);

namespace App\Circle\Application\Command;

final readonly class ChangeCircleVisibilityCommand
{
    public function __construct(
        public string $circleId,
        public string $visibility,
        public string $updatedBy
    ) {
    }
}
