<?php

declare(strict_types=1);

namespace App\Post\Application\DTO;

final readonly class PostListDTO
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
