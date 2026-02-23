<?php

declare(strict_types=1);

namespace App\Search\Application\DTO;

use DateTimeImmutable;

final readonly class SearchUserDTO
{
    /**
     * @param array<string> $roles
     */
    public function __construct(
        public string $id,
        public string $username,
        public array $roles,
        public DateTimeImmutable $createdAt
    ) {
    }
}
