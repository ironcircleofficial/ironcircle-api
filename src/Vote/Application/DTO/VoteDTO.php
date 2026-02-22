<?php

declare(strict_types=1);

namespace App\Vote\Application\DTO;

use DateTimeImmutable;

final readonly class VoteDTO
{
    public function __construct(
        public string $id,
        public string $userId,
        public string $targetType,
        public string $targetId,
        public int $value,
        public DateTimeImmutable $createdAt
    ) {
    }
}
