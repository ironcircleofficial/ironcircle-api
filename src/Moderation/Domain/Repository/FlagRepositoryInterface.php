<?php

declare(strict_types=1);

namespace App\Moderation\Domain\Repository;

use App\Moderation\Domain\Model\Flag;

interface FlagRepositoryInterface
{
    public function save(Flag $flag): void;

    public function findById(string $id): ?Flag;

    public function findByReporterAndTarget(string $reporterId, string $targetType, string $targetId): ?Flag;

    /** @return array<Flag> */
    public function findPendingFlags(int $limit, int $offset): array;

    public function countPendingFlags(): int;

    public function delete(Flag $flag): void;
}
