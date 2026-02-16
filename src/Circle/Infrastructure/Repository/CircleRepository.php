<?php

declare(strict_types=1);

namespace App\Circle\Infrastructure\Repository;

use App\Circle\Domain\Model\Circle;
use App\Circle\Domain\Repository\CircleRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

final class CircleRepository implements CircleRepositoryInterface
{
    private DocumentRepository $repository;

    public function __construct(
        private readonly DocumentManager $documentManager
    ) {
        $this->repository = $this->documentManager->getRepository(Circle::class);
    }

    public function save(Circle $circle): void
    {
        $this->documentManager->persist($circle);
        $this->documentManager->flush();
    }

    public function findById(string $id): ?Circle
    {
        return $this->repository->find($id);
    }

    public function findBySlug(string $slug): ?Circle
    {
        return $this->repository->findOneBy(['slug' => $slug]);
    }

    public function existsBySlug(string $slug): bool
    {
        return $this->repository->findOneBy(['slug' => $slug]) !== null;
    }

    public function existsByName(string $name): bool
    {
        return $this->repository->findOneBy(['name' => $name]) !== null;
    }

    public function findAll(int $limit = 20, int $offset = 0): array
    {
        return $this->repository->findBy(
            [],
            ['createdAt' => 'DESC'],
            $limit,
            $offset
        );
    }

    public function findPublicCircles(int $limit = 20, int $offset = 0): array
    {
        return $this->repository->findBy(
            ['visibility' => 'public'],
            ['createdAt' => 'DESC'],
            $limit,
            $offset
        );
    }

    public function findByCreator(string $creatorId, int $limit = 20, int $offset = 0): array
    {
        return $this->repository->findBy(
            ['creatorId' => $creatorId],
            ['createdAt' => 'DESC'],
            $limit,
            $offset
        );
    }

    public function count(): int
    {
        return $this->repository->createQueryBuilder()
            ->count()
            ->getQuery()
            ->execute();
    }

    public function countPublic(): int
    {
        return $this->repository->createQueryBuilder()
            ->field('visibility')->equals('public')
            ->count()
            ->getQuery()
            ->execute();
    }

    public function delete(Circle $circle): void
    {
        $this->documentManager->remove($circle);
        $this->documentManager->flush();
    }
}
