<?php

declare(strict_types=1);

namespace App\Circle\Application\DTO;

use App\User\Application\DTO\UserInlineDTO;
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
        public UserInlineDTO $creator,
        public array $moderatorIds,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt
    ) {
    }
}
