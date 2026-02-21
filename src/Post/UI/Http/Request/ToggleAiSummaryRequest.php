<?php

declare(strict_types=1);

namespace App\Post\UI\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class ToggleAiSummaryRequest
{
    public function __construct(
        #[Assert\NotNull(message: 'Enabled field is required')]
        public bool $enabled
    ) {
    }
}
