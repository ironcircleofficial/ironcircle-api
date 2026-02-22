<?php

declare(strict_types=1);

namespace App\Moderation\Application\Query;

final readonly class ListPendingFlagsQuery
{
    public function __construct(
        public int $limit,
        public int $offset
    ) {
    }
}
