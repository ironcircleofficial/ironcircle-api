<?php

declare(strict_types=1);

namespace App\Comment\UI\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateCommentRequest
{
    public function __construct(
        #[Assert\NotBlank(message: 'Content must not be empty')]
        #[Assert\Length(
            min: 1,
            max: 10000,
            minMessage: 'Content must be at least 1 character',
            maxMessage: 'Content must not exceed 10000 characters'
        )]
        public string $content,

        #[Assert\Type(type: 'string')]
        public ?string $parentCommentId = null
    ) {
    }
}
