<?php

declare(strict_types=1);

namespace App\Circle\Application\Query;

final readonly class ListCirclesByCreatorQuery
{
    /**
     * @param int<1, max> $limit
     * @param int<0, max> $offset
     */
    public function __construct(
        public string $creatorId,
        public int $limit = 20,
        public int $offset = 0
    ) {
    }
}
