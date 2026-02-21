<?php

declare(strict_types=1);

namespace App\Post\Application\Command;

final readonly class UploadPostAttachmentCommand
{
    public function __construct(
        public string $postId,
        public string $authorId,
        public string $tempFilePath,
        public string $originalFilename,
        public string $mimeType,
        public int $size
    ) {
    }
}
