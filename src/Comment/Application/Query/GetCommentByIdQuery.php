<?php

declare(strict_types=1);

namespace App\Comment\Application\Query;

final readonly class GetCommentByIdQuery
{
    public function __construct(
        public string $id
    ) {
    }
}
