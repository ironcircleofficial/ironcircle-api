<?php

declare(strict_types=1);

namespace App\Post\Infrastructure\Repository;

use App\Post\Domain\Model\Post;
use App\Post\Domain\Repository\PostRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

final class PostRepository implements PostRepositoryInterface
{
    private DocumentRepository $repository;

    public function __construct(
        private readonly DocumentManager $documentManager
    ) {
        $this->repository = $this->documentManager->getRepository(Post::class);
    }

    public function save(Post $post): void
    {
        $this->documentManager->persist($post);
        $this->documentManager->flush();
    }

    public function findById(string $id): ?Post
    {
        return $this->repository->find($id);
    }

    /**
     * @return array<Post>
     */
    public function findByCircle(string $circleId, int $limit = 20, int $offset = 0): array
    {
        return $this->repository->findBy(
            ['circleId' => $circleId],
            ['createdAt' => 'DESC'],
            $limit,
            $offset
        );
    }

    public function countByCircle(string $circleId): int
    {
        return $this->repository->createQueryBuilder()
            ->field('circleId')->equals($circleId)
            ->count()
            ->getQuery()
            ->execute();
    }

    public function delete(Post $post): void
    {
        $this->documentManager->remove($post);
        $this->documentManager->flush();
    }
}
