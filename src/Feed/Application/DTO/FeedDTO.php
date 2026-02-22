<?php

declare(strict_types=1);

namespace App\Feed\Application\DTO;

use App\Post\Application\DTO\PostDTO;

final readonly class FeedDTO
{
    /**
     * @param array<PostDTO> $posts
     */
    public function __construct(
        public array $posts,
        public int $total,
        public int $limit,
        public int $offset
    ) {
    }
}
