<?php

declare(strict_types=1);

namespace App\Search\Application\DTO;

use App\Post\Application\DTO\PostDTO;

final readonly class SearchPostsResultDTO
{
    /**
     * @param array<PostDTO> $posts
     */
    public function __construct(
        public string $query,
        public array $posts,
        public int $total,
        public int $limit,
        public int $offset
    ) {
    }
}
