<?php

declare(strict_types=1);

namespace App\Post\Application\Command;

final readonly class DeletePostCommand
{
    public function __construct(
        public string $postId,
        public string $deletedBy
    ) {
    }
}
