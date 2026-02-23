<?php

declare(strict_types=1);

namespace App\Search\Application\Query;

final readonly class SearchUsersQuery
{
    public function __construct(
        public string $query,
        public int $limit = 20,
        public int $offset = 0
    ) {
    }
}
