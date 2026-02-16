<?php

declare(strict_types=1);

namespace App\Circle\UI\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ChangeCircleVisibilityRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Visibility must not be empty')]
        #[Assert\Choice(
            choices: ['public', 'private'],
            message: 'Visibility must be either "public" or "private"'
        )]
        public string $visibility
    ) {
    }
}
