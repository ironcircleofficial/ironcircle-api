<?php

declare(strict_types=1);

namespace App\Post\Application\DTO;

use DateTimeImmutable;

final readonly class PostDTO
{
    /**
     * @param array<string> $imageUrls
     */
    public function __construct(
        public string $id,
        public string $circleId,
        public string $authorId,
        public string $title,
        public string $content,
        public array $imageUrls,
        public bool $aiSummaryEnabled,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt
    ) {
    }
}
