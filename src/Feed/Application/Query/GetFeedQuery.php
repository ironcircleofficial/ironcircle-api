<?php

declare(strict_types=1);

namespace App\Feed\Application\Query;

final readonly class GetFeedQuery
{
    public function __construct(
        public ?string $userId = null,
        public int $limit = 20,
        public int $offset = 0
    ) {
    }
}
