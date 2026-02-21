<?php

declare(strict_types=1);

namespace App\Post\Application\Query;

final readonly class ListPostAttachmentsByPostQuery
{
    public function __construct(
        public string $postId
    ) {
    }
}
