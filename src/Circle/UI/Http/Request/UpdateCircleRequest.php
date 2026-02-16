<?php

declare(strict_types=1);

namespace App\Circle\UI\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateCircleRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Circle name must not be empty')]
        #[Assert\Length(
            min: 3,
            max: 100,
            minMessage: 'Circle name must be at least {{ limit }} characters long',
            maxMessage: 'Circle name cannot be longer than {{ limit }} characters'
        )]
        public string $name,

        #[Assert\NotBlank(message: 'Description must not be empty')]
        #[Assert\Length(
            min: 10,
            max: 500,
            minMessage: 'Description must be at least {{ limit }} characters long',
            maxMessage: 'Description cannot be longer than {{ limit }} characters'
        )]
        public string $description
    ) {
    }
}
