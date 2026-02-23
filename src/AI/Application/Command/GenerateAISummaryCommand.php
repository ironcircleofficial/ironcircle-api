<?php

declare(strict_types=1);

namespace App\AI\Application\Command;

final readonly class GenerateAISummaryCommand
{
    public function __construct(
        public string $postId,
        public string $requestedBy
    ) {
    }
}
