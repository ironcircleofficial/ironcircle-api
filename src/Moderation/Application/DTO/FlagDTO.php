<?php

declare(strict_types=1);

namespace App\Moderation\Application\DTO;

use DateTimeImmutable;

final readonly class FlagDTO
{
    public function __construct(
        public string $id,
        public string $targetType,
        public string $targetId,
        public string $reporterId,
        public string $reason,
        public string $status,
        public ?string $resolvedById,
        public ?DateTimeImmutable $resolvedAt,
        public DateTimeImmutable $createdAt
    ) {
    }
}
