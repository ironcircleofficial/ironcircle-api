<?php

declare(strict_types=1);

namespace App\Moderation\Infrastructure\Repository;

use App\Moderation\Domain\Model\Flag;
use App\Moderation\Domain\Repository\FlagRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

final class FlagRepository implements FlagRepositoryInterface
{
    private DocumentRepository $repository;

    public function __construct(
        private readonly DocumentManager $documentManager
    ) {
        $this->repository = $this->documentManager->getRepository(Flag::class);
    }

    public function save(Flag $flag): void
    {
        $this->documentManager->persist($flag);
        $this->documentManager->flush();
    }

    public function findById(string $id): ?Flag
    {
        return $this->repository->find($id);
    }

    public function findByReporterAndTarget(string $reporterId, string $targetType, string $targetId): ?Flag
    {
        return $this->repository->findOneBy([
            'reporterId' => $reporterId,
            'targetType' => $targetType,
            'targetId'   => $targetId,
        ]);
    }

    /** @return array<Flag> */
    public function findPendingFlags(int $limit, int $offset): array
    {
        return $this->repository->findBy(
            ['status' => 'pending'],
            ['createdAt' => 'asc'],
            $limit,
            $offset
        );
    }

    public function countPendingFlags(): int
    {
        return (int) $this->repository->createQueryBuilder()
            ->field('status')->equals('pending')
            ->count()
            ->getQuery()
            ->execute();
    }

    public function delete(Flag $flag): void
    {
        $this->documentManager->remove($flag);
        $this->documentManager->flush();
    }
}
