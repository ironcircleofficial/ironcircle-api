<?php

declare(strict_types=1);

namespace App\Post\Application\Command;

final readonly class CreatePostCommand
{
    /**
     * @param array<string> $imageUrls
     */
    public function __construct(
        public string $circleId,
        public string $authorId,
        public string $title,
        public string $content,
        public array $imageUrls = [],
        public bool $aiSummaryEnabled = false
    ) {
    }
}
