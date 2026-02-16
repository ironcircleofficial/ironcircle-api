<?php

declare(strict_types=1);

namespace App\Circle\Application\DTO;

use DateTimeImmutable;

final readonly class CircleDTO
{
    /**
     * @param array<string> $moderatorIds
     */
    public function __construct(
        public string $id,
        public string $name,
        public string $slug,
        public string $description,
        public string $visibility,
        public string $creatorId,
        public array $moderatorIds,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt
    ) {
    }
}
