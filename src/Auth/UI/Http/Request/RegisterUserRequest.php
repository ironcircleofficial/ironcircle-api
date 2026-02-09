<?php

declare(strict_types=1);

namespace App\Auth\UI\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class RegisterUserRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Username must not be empty')]
        #[Assert\Length(
            min: 3,
            max: 30,
            minMessage: 'Username must be at least {{ limit }} characters long',
            maxMessage: 'Username cannot be longer than {{ limit }} characters'
        )]
        #[Assert\Regex(
            pattern: '/^[a-zA-Z0-9_]+$/',
            message: 'Username can only contain letters, numbers, and underscores'
        )]
        public string $username,

        #[Assert\NotBlank(message: 'Email must not be empty')]
        #[Assert\Email(message: 'Email must be a valid email address')]
        public string $email,

        #[Assert\NotBlank(message: 'Password must not be empty')]
        #[Assert\Length(
            min: 8,
            minMessage: 'Password must be at least {{ limit }} characters long'
        )]
        public string $password
    ) {
    }
}
