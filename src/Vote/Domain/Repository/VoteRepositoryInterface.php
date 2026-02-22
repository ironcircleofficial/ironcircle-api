<?php

declare(strict_types=1);

namespace App\Vote\Domain\Repository;

use App\Vote\Domain\Model\Vote;

interface VoteRepositoryInterface
{
    public function save(Vote $vote): void;

    public function findByUserAndTarget(string $userId, string $targetType, string $targetId): ?Vote;

    public function delete(Vote $vote): void;
}
