<?php

declare(strict_types=1);

namespace App\Search\Application\DTO;

final readonly class SearchUsersResultDTO
{
    /**
     * @param array<SearchUserDTO> $users
     */
    public function __construct(
        public string $query,
        public array $users,
        public int $total,
        public int $limit,
        public int $offset
    ) {
    }
}
