<?php

declare(strict_types=1);

namespace App\Admin\Application\Query;

final readonly class GetUserByIdQuery
{
    public function __construct(
        public string $userId
    ) {
    }
}
