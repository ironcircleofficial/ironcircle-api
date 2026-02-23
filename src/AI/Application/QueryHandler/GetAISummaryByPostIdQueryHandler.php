<?php

declare(strict_types=1);

namespace App\AI\Application\QueryHandler;

use App\AI\Application\DTO\AISummaryDTO;
use App\AI\Application\Query\GetAISummaryByPostIdQuery;
use App\AI\Domain\Exception\AISummaryNotFoundException;
use App\AI\Domain\Repository\AISummaryRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetAISummaryByPostIdQueryHandler
{
    public function __construct(
        private AISummaryRepositoryInterface $aiSummaryRepository
    ) {
    }

    public function __invoke(GetAISummaryByPostIdQuery $query): AISummaryDTO
    {
        $summary = $this->aiSummaryRepository->findByPostId($query->postId);

        if ($summary === null) {
            throw AISummaryNotFoundException::forPost($query->postId);
        }

        return new AISummaryDTO(
            id: $summary->getId(),
            postId: $summary->getPostId(),
            summary: $summary->getSummary(),
            model: $summary->getModel(),
            generatedAt: $summary->getGeneratedAt()
        );
    }
}
