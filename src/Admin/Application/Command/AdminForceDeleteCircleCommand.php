<?php

declare(strict_types=1);

namespace App\Admin\Application\Command;

final readonly class AdminForceDeleteCircleCommand
{
    public function __construct(
        public string $circleId,
        public string $deletedBy
    ) {
    }
}
