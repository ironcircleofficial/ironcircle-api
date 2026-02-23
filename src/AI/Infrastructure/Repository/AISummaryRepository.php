<?php

declare(strict_types=1);

namespace App\AI\Infrastructure\Repository;

use App\AI\Domain\Model\AISummary;
use App\AI\Domain\Repository\AISummaryRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

final class AISummaryRepository implements AISummaryRepositoryInterface
{
    private DocumentRepository $repository;

    public function __construct(
        private readonly DocumentManager $documentManager
    ) {
        $this->repository = $this->documentManager->getRepository(AISummary::class);
    }

    public function save(AISummary $summary): void
    {
        $this->documentManager->persist($summary);
        $this->documentManager->flush();
    }

    public function findByPostId(string $postId): ?AISummary
    {
        return $this->repository->findOneBy(['postId' => $postId]);
    }

    public function deleteByPostId(string $postId): void
    {
        $summary = $this->findByPostId($postId);

        if ($summary !== null) {
            $this->documentManager->remove($summary);
            $this->documentManager->flush();
        }
    }
}
