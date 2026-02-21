<?php

declare(strict_types=1);

namespace App\Post\Application\Command;

final readonly class DeletePostAttachmentCommand
{
    public function __construct(
        public string $attachmentId,
        public string $deletedBy
    ) {
    }
}
