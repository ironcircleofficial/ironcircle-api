<?php

declare(strict_types=1);

namespace App\Circle\Application\Query;

final readonly class GetCircleBySlugQuery
{
    public function __construct(
        public string $slug
    ) {
    }
}
