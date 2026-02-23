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

    public function findByCircleIds(array $circleIds, int $limit = 20, int $offset = 0): array
    {
        if (empty($circleIds)) {
            return [];
        }

        return $this->repository->createQueryBuilder()
            ->field('circleId')->in($circleIds)
            ->sort('createdAt', 'DESC')
            ->limit($limit)
            ->skip($offset)
            ->getQuery()
            ->execute()
            ->toArray();
    }

    public function countByCircleIds(array $circleIds): int
    {
        if (empty($circleIds)) {
            return 0;
        }

        return $this->repository->createQueryBuilder()
            ->field('circleId')->in($circleIds)
            ->count()
            ->getQuery()
            ->execute();
    }

    public function delete(Post $post): void
    {
        $this->documentManager->remove($post);
        $this->documentManager->flush();
    }

    public function searchByQuery(string $query, array $accessibleCircleIds, int $limit = 20, int $offset = 0): array
    {
        if (empty($accessibleCircleIds)) {
            return [];
        }

        $qb = $this->repository->createQueryBuilder();
        $regex = new \MongoDB\BSON\Regex(preg_quote($query, '/'), 'i');

        $qb->addOr(
            $qb->expr()->field('title')->equals($regex),
            $qb->expr()->field('content')->equals($regex)
        );

        $qb->field('circleId')->in($accessibleCircleIds);

        return $qb->sort('createdAt', 'DESC')
            ->limit($limit)
            ->skip($offset)
            ->getQuery()
            ->execute()
            ->toArray();
    }

    public function countSearchByQuery(string $query, array $accessibleCircleIds): int
    {
        if (empty($accessibleCircleIds)) {
            return 0;
        }

        $qb = $this->repository->createQueryBuilder();
        $regex = new \MongoDB\BSON\Regex(preg_quote($query, '/'), 'i');

        $qb->addOr(
            $qb->expr()->field('title')->equals($regex),
            $qb->expr()->field('content')->equals($regex)
        );

        $qb->field('circleId')->in($accessibleCircleIds);

        return (int) $qb->count()->getQuery()->execute();
    }
}
