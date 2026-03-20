<?php

declare(strict_types=1);

namespace App\Moderation\Application\DTO;

use App\User\Application\DTO\UserInlineDTO;
use DateTimeImmutable;

final readonly class FlagDTO
{
    public function __construct(
        public string $id,
        public string $targetType,
        public string $targetId,
        public UserInlineDTO $reporter,
        public string $reason,
        public string $status,
        public ?UserInlineDTO $resolvedBy,
        public ?DateTimeImmutable $resolvedAt,
        public DateTimeImmutable $createdAt
    ) {
    }
}
