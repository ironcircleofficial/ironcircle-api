<?php

declare(strict_types=1);

namespace App\Circle\Application\Command;

final readonly class CreateCircleCommand
{
    public function __construct(
        public string $name,
        public string $description,
        public string $visibility,
        public string $creatorId
    ) {
    }
}
