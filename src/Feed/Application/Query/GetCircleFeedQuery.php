<?php

declare(strict_types=1);

namespace App\Feed\Application\Query;

final readonly class GetCircleFeedQuery
{
    public function __construct(
        public string $circleId,
        public int $limit = 20,
        public int $offset = 0
    ) {
    }
}
