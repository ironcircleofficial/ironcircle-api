<?php

declare(strict_types=1);

namespace App\Post\Application\Command;

final readonly class UpdatePostCommand
{
    public function __construct(
        public string $postId,
        public string $updatedBy,
        public string $title,
        public string $content
    ) {
    }
}
