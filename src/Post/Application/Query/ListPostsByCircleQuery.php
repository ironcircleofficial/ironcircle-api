<?php

declare(strict_types=1);

namespace App\Post\Application\Query;

final readonly class ListPostsByCircleQuery
{
    public function __construct(
        public string $circleId,
        public int $limit = 20,
        public int $offset = 0
    ) {
    }
}
