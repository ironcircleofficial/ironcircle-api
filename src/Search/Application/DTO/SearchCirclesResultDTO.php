<?php

declare(strict_types=1);

namespace App\Search\Application\DTO;

use App\Circle\Application\DTO\CircleDTO;

final readonly class SearchCirclesResultDTO
{
    /**
     * @param array<CircleDTO> $circles
     */
    public function __construct(
        public string $query,
        public array $circles,
        public int $total,
        public int $limit,
        public int $offset
    ) {
    }
}
