<?php

declare(strict_types=1);

namespace App\Comment\Application\Command;

final readonly class CreateCommentCommand
{
    public function __construct(
        public string $postId,
        public string $authorId,
        public string $content,
        public ?string $parentCommentId = null
    ) {
    }
}
