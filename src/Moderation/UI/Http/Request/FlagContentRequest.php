<?php

declare(strict_types=1);

namespace App\Moderation\UI\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class FlagContentRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Target type must not be empty')]
        #[Assert\Choice(
            choices: ['post', 'comment'],
            message: 'Target type must be "post" or "comment"'
        )]
        public string $targetType,

        #[Assert\NotBlank(message: 'Target ID must not be empty')]
        public string $targetId,

        #[Assert\NotBlank(message: 'Reason must not be empty')]
        #[Assert\Length(
            min: 10,
            max: 1000,
            minMessage: 'Reason must be at least 10 characters',
            maxMessage: 'Reason must not exceed 1000 characters'
        )]
        public string $reason
    ) {
    }
}
