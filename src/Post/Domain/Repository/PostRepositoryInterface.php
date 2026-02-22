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

    /**
     * @param array<string> $circleIds
     * @param int<1, max> $limit
     * @param int<0, max> $offset
     * @return array<Post>
     */
    public function findByCircleIds(array $circleIds, int $limit = 20, int $offset = 0): array;

    /**
     * @param array<string> $circleIds
     */
    public function countByCircleIds(array $circleIds): int;

    public function delete(Post $post): void;
}
