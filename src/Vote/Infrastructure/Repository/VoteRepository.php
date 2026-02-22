<?php

declare(strict_types=1);

namespace App\Vote\Infrastructure\Repository;

use App\Vote\Domain\Model\Vote;
use App\Vote\Domain\Repository\VoteRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

final class VoteRepository implements VoteRepositoryInterface
{
    private DocumentRepository $repository;

    public function __construct(
        private readonly DocumentManager $documentManager
    ) {
        $this->repository = $this->documentManager->getRepository(Vote::class);
    }

    public function save(Vote $vote): void
    {
        $this->documentManager->persist($vote);
        $this->documentManager->flush();
    }

    public function findByUserAndTarget(string $userId, string $targetType, string $targetId): ?Vote
    {
        return $this->repository->findOneBy([
            'userId' => $userId,
            'targetType' => $targetType,
            'targetId' => $targetId,
        ]);
    }

    public function delete(Vote $vote): void
    {
        $this->documentManager->remove($vote);
        $this->documentManager->flush();
    }
}
