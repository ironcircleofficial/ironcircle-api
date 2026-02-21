<?php

declare(strict_types=1);

namespace App\Post\Application\Command;

final readonly class ToggleAiSummaryCommand
{
    public function __construct(
        public string $postId,
        public bool $enabled,
        public string $updatedBy
    ) {
    }
}
