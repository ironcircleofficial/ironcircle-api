<?php

declare(strict_types=1);

namespace App\Comment\Application\Command;

final readonly class DeleteCommentCommand
{
    public function __construct(
        public string $commentId,
        public string $deletedBy
    ) {
    }
}
