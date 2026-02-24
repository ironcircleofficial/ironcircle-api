<?php

declare(strict_types=1);

namespace App\Admin\Application\Command;

final readonly class UpdateUserRolesCommand
{
    /**
     * @param array<string> $roles
     */
    public function __construct(
        public string $userId,
        public array $roles
    ) {
    }
}
