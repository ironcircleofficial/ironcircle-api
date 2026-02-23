<?php

declare(strict_types=1);

namespace App\AI\Application\DTO;

use DateTimeImmutable;

final readonly class AISummaryDTO
{
    public function __construct(
        public string $id,
        public string $postId,
        public string $summary,
        public string $model,
        public DateTimeImmutable $generatedAt
    ) {
    }
}
