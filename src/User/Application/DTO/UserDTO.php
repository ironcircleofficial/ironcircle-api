<?php

declare(strict_types=1);

namespace App\User\Application\DTO;

use DateTimeImmutable;

final readonly class UserDTO
{
    /**
     * @param array<string> $roles
     */
    public function __construct(
        public string $id,
        public string $username,
        public string $email,
        public array $roles,
        public DateTimeImmutable $createdAt,
        public DateTimeImmutable $updatedAt
    ) {
    }
}
