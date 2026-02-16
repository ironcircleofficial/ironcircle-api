<?php

declare(strict_types=1);

namespace App\Circle\Application\Command;

final readonly class UpdateCircleCommand
{
    public function __construct(
        public string $circleId,
        public string $name,
        public string $description,
        public string $updatedBy
    ) {
    }
}
