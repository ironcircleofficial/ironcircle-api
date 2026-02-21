<?php

declare(strict_types=1);

namespace App\Post\UI\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreatePostRequest
{
    /**
     * @param array<string> $imageUrls
     */
    public function __construct(
        #[Assert\NotBlank(message: 'Title must not be empty')]
        #[Assert\Length(
            min: 3,
            max: 255,
            minMessage: 'Title must be at least 3 characters',
            maxMessage: 'Title must not exceed 255 characters'
        )]
        public string $title,

        #[Assert\NotBlank(message: 'Content must not be empty')]
        #[Assert\Length(min: 10, minMessage: 'Content must be at least 10 characters')]
        public string $content,

        #[Assert\All([new Assert\Type('string')])]
        public array $imageUrls = [],

        public bool $aiSummaryEnabled = false
    ) {
    }
}
