<?php

declare(strict_types=1);

namespace App\Post\Domain\Repository;

use App\Post\Domain\Model\PostAttachment;

interface PostAttachmentRepositoryInterface
{
    public function save(PostAttachment $attachment): void;

    public function findById(string $id): ?PostAttachment;

    /**
     * @return array<PostAttachment>
     */
    public function findByPostId(string $postId): array;

    /**
     * @param array<string> $postIds
     * @return array<PostAttachment>
     */
    public function findByPostIds(array $postIds): array;

    public function delete(PostAttachment $attachment): void;

    public function deleteByPostId(string $postId): void;
}
