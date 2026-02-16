<?php

declare(strict_types=1);

namespace App\Circle\Application\Query;

final readonly class ListCirclesQuery
{
    /**
     * @param int<1, max> $limit
     * @param int<0, max> $offset
     */
    public function __construct(
        public int $limit = 20,
        public int $offset = 0,
        public bool $publicOnly = false
    ) {
    }
}
