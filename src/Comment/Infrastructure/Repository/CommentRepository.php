<?php

declare(strict_types=1);

namespace App\Comment\Infrastructure\Repository;

use App\Comment\Domain\Model\Comment;
use App\Comment\Domain\Repository\CommentRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

final class CommentRepository implements CommentRepositoryInterface
{
    private DocumentRepository $repository;

    public function __construct(
        private readonly DocumentManager $documentManager
    ) {
        $this->repository = $this->documentManager->getRepository(Comment::class);
    }

    public function save(Comment $comment): void
    {
        $this->documentManager->persist($comment);
        $this->documentManager->flush();
    }

    public function findById(string $id): ?Comment
    {
        return $this->repository->find($id);
    }

    /**
     * @return array<Comment>
     */
    public function findByPostId(string $postId, int $limit = 20, int $offset = 0): array
    {
        return $this->repository->findBy(
            ['postId' => $postId],
            ['createdAt' => 'ASC'],
            $limit,
            $offset
        );
    }

    public function countByPostId(string $postId): int
    {
        return $this->repository->createQueryBuilder()
            ->field('postId')->equals($postId)
            ->count()
            ->getQuery()
            ->execute();
    }

    public function delete(Comment $comment): void
    {
        $this->documentManager->remove($comment);
        $this->documentManager->flush();
    }

    public function deleteByPostId(string $postId): void
    {
        $this->documentManager->createQueryBuilder(Comment::class)
            ->remove()
            ->field('postId')->equals($postId)
            ->getQuery()
            ->execute();
    }
}
