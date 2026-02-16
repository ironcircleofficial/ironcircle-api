<?php

declare(strict_types=1);

namespace App\Circle\Application\DTO;

final readonly class CircleListDTO
{
    /**
     * @param array<CircleDTO> $circles
     */
    public function __construct(
        public array $circles,
        public int $total,
        public int $limit,
        public int $offset
    ) {
    }
}
