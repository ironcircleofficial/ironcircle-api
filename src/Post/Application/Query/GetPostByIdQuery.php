<?php

declare(strict_types=1);

namespace App\Post\Application\Query;

final readonly class GetPostByIdQuery
{
    public function __construct(
        public string $id
    ) {
    }
}
