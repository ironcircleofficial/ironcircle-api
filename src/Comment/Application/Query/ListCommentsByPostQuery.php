<?php

declare(strict_types=1);

namespace App\Comment\Application\Query;

final readonly class ListCommentsByPostQuery
{
    public function __construct(
        public string $postId,
        public int $limit = 20,
        public int $offset = 0
    ) {
    }
}
