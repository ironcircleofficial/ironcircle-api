<?php

declare(strict_types=1);

namespace App\Comment\Application\DTO;

use App\User\Application\DTO\UserInlineDTO;
use DateTimeImmutable;

final readonly class CommentDTO
{
    public function __construct(
        public string $id,
        public string $postId,
        public UserInlineDTO $author,
        public ?string $parentCommentId,
        public string $content,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt
    ) {
    }
}
