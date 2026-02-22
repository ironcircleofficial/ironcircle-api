<?php

declare(strict_types=1);

namespace App\Moderation\Application\Query;

final readonly class GetFlagByIdQuery
{
    public function __construct(
        public string $flagId
    ) {
    }
}
