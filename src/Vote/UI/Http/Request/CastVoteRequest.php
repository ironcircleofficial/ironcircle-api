<?php

declare(strict_types=1);

namespace App\Vote\UI\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CastVoteRequest
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

        #[Assert\NotNull(message: 'Value must not be null')]
        #[Assert\Choice(
            choices: [1, -1],
            message: 'Value must be 1 (upvote) or -1 (downvote)'
        )]
        public int $value
    ) {
    }
}
