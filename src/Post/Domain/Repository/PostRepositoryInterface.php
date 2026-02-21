<?php

declare(strict_types=1);

namespace App\Post\Domain\Repository;

use App\Post\Domain\Model\Post;

interface PostRepositoryInterface
{
    public function save(Post $post): void;

    public function findById(string $id): ?Post;

    /**
     * @param int<1, max> $limit
     * @param int<0, max> $offset
     * @return array<Post>
     */
    public function findByCircle(string $circleId, int $limit = 20, int $offset = 0): array;

    public function countByCircle(string $circleId): int;

    public function delete(Post $post): void;
}
