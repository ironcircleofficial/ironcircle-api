<?php

declare(strict_types=1);

namespace App\Post\Application\DTO;

use App\User\Application\DTO\UserInlineDTO;
use DateTimeImmutable;

final readonly class PostDTO
{
    /**
     * @param array<PostAttachmentDTO> $attachments
     */
    public function __construct(
        public string $id,
        public string $circleId,
        public UserInlineDTO $author,
        public string $title,
        public string $content,
        public bool $aiSummaryEnabled,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt,
        public array $attachments = []
    ) {
    }
}
