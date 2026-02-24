<?php

declare(strict_types=1);

namespace App\Admin\Application\DTO;

final readonly class AdminUserListDTO
{
    /**
     * @param array<AdminUserDTO> $users
     */
    public function __construct(
        public array $users,
        public int $total,
        public int $limit,
        public int $offset
    ) {
    }
}
