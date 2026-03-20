<?php

declare(strict_types=1);

namespace App\Post\Application\DTO;

use App\User\Application\DTO\UserInlineDTO;
use DateTimeImmutable;

final readonly class PostAttachmentDTO
{
    public function __construct(
        public string $id,
        public string $postId,
        public UserInlineDTO $author,
        public string $originalFilename,
        public string $storedFilename,
        public string $mimeType,
        public int $size,
        public DateTimeImmutable $uploadedAt
    ) {
    }
}
