<?php

declare(strict_types=1);

namespace App\Admin\Application\Command;

final readonly class AdminDeleteUserCommand
{
    public function __construct(
        public string $userId
    ) {
    }
}
