<?php

declare(strict_types=1);

namespace App\Admin\Application\Query;

final readonly class ListAllCirclesQuery
{
    public function __construct(
        public int $limit = 20,
        public int $offset = 0
    ) {
    }
}
