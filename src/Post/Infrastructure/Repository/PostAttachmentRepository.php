<?php

declare(strict_types=1);

namespace App\Post\Infrastructure\Repository;

use App\Post\Domain\Model\PostAttachment;
use App\Post\Domain\Repository\PostAttachmentRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

final class PostAttachmentRepository implements PostAttachmentRepositoryInterface
{
    private DocumentRepository $repository;

    public function __construct(
        private readonly DocumentManager $documentManager
    ) {
        $this->repository = $this->documentManager->getRepository(PostAttachment::class);
    }

    public function save(PostAttachment $attachment): void
    {
        $this->documentManager->persist($attachment);
        $this->documentManager->flush();
    }

    public function findById(string $id): ?PostAttachment
    {
        return $this->repository->find($id);
    }

    /**
     * @return array<PostAttachment>
     */
    public function findByPostId(string $postId): array
    {
        return $this->repository->findBy(
            ['postId' => $postId],
            ['uploadedAt' => 'DESC']
        );
    }

    /**
     * @param array<string> $postIds
     * @return array<PostAttachment>
     */
    public function findByPostIds(array $postIds): array
    {
        if (empty($postIds)) {
            return [];
        }

        return $this->repository->createQueryBuilder()
            ->field('postId')->in($postIds)
            ->sort('uploadedAt', 'DESC')
            ->getQuery()
            ->execute()
            ->toArray();
    }

    public function delete(PostAttachment $attachment): void
    {
        $this->documentManager->remove($attachment);
        $this->documentManager->flush();
    }

    public function deleteByPostId(string $postId): void
    {
        $this->repository->createQueryBuilder()
            ->remove()
            ->field('postId')->equals($postId)
            ->getQuery()
            ->execute();
    }
}
