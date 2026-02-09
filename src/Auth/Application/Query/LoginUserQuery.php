<?php

declare(strict_types=1);

namespace App\Auth\Application\Query;

final readonly class LoginUserQuery
{
    public function __construct(
        public string $username,
        public string $password
    ) {
    }
}
