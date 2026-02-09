<?php

declare(strict_types=1);

namespace App\Auth\Application\DTO;

final readonly class TokenDTO
{
    public function __construct(
        public string $token,
        public string $userId,
        public string $username
    ) {
    }
}
