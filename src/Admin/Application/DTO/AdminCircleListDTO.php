<?php

declare(strict_types=1);

namespace App\Admin\Application\DTO;

use App\Circle\Application\DTO\CircleDTO;

final readonly class AdminCircleListDTO
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
