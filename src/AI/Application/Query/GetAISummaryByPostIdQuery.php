<?php

declare(strict_types=1);

namespace App\AI\Application\Query;

final readonly class GetAISummaryByPostIdQuery
{
    public function __construct(
        public string $postId
    ) {
    }
}
