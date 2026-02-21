<?php

declare(strict_types=1);

namespace App\Comment\Domain\Repository;

use App\Comment\Domain\Model\Comment;

interface CommentRepositoryInterface
{
    public function save(Comment $comment): void;

    public function findById(string $id): ?Comment;

    /**
     * @param int<1, max> $limit
     * @param int<0, max> $offset
     * @return array<Comment>
     */
    public function findByPostId(string $postId, int $limit = 20, int $offset = 0): array;

    public function countByPostId(string $postId): int;

    public function delete(Comment $comment): void;

    public function deleteByPostId(string $postId): void;
}
