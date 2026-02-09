<?php

declare(strict_types=1);

namespace App\Auth\UI\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class LoginUserRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Username must not be empty')]
        public string $username,

        #[Assert\NotBlank(message: 'Password must not be empty')]
        public string $password
    ) {
    }
}
