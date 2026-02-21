<?php

declare(strict_types=1);

namespace App\Comment\Application\DTO;

final readonly class CommentListDTO
{
    /**
     * @param array<CommentDTO> $comments
     */
    public function __construct(
        public array $comments,
        public int $total,
        public int $limit,
        public int $offset
    ) {
    }
}
