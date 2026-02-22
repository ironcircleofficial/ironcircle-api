<?php

declare(strict_types=1);

namespace App\Moderation\Application\DTO;

final readonly class FlagListDTO
{
    /**
     * @param array<FlagDTO> $flags
     */
    public function __construct(
        public array $flags,
        public int $total,
        public int $limit,
        public int $offset
    ) {
    }
}
