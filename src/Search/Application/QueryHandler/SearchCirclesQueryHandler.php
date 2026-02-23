<?php

declare(strict_types=1);

namespace App\Search\Application\QueryHandler;

use App\Circle\Application\DTO\CircleDTO;
use App\Circle\Domain\Model\Circle;
use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Search\Application\DTO\SearchCirclesResultDTO;
use App\Search\Application\Query\SearchCirclesQuery;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SearchCirclesQueryHandler
{
    public function __construct(
        private CircleRepositoryInterface $circleRepository
    ) {
    }

    public function __invoke(SearchCirclesQuery $query): SearchCirclesResultDTO
    {
        $circles = $this->circleRepository->searchByQuery(
            $query->query,
            $query->userId,
            $query->limit,
            $query->offset
        );

        $total = $this->circleRepository->countSearchByQuery($query->query, $query->userId);

        $circleDTOs = array_map(
            fn(Circle $circle) => new CircleDTO(
                id: $circle->getId(),
                name: $circle->getName(),
                slug: $circle->getSlug(),
                description: $circle->getDescription(),
                visibility: $circle->getVisibility(),
                creatorId: $circle->getCreatorId(),
                moderatorIds: $circle->getModeratorIds(),
                createdAt: $circle->getCreatedAt(),
                updatedAt: $circle->getUpdatedAt()
            ),
            $circles
        );

        return new SearchCirclesResultDTO(
            query: $query->query,
            circles: $circleDTOs,
            total: $total,
            limit: $query->limit,
            offset: $query->offset
        );
    }
}
