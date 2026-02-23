<?php

declare(strict_types=1);

namespace App\AI\Domain\Repository;

use App\AI\Domain\Model\AISummary;

interface AISummaryRepositoryInterface
{
    public function save(AISummary $summary): void;

    public function findByPostId(string $postId): ?AISummary;

    public function deleteByPostId(string $postId): void;
}
