<?php

declare(strict_types=1);

namespace App\Circle\Application\QueryHandler;

use App\Circle\Application\DTO\CircleDTO;
use App\Circle\Application\DTO\CircleListDTO;
use App\Circle\Application\Query\ListCirclesByCreatorQuery;
use App\Circle\Domain\Repository\CircleRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListCirclesByCreatorQueryHandler
{
    public function __construct(
        private CircleRepositoryInterface $circleRepository
    ) {
    }

    public function __invoke(ListCirclesByCreatorQuery $query): CircleListDTO
    {
        $circles = $this->circleRepository->findByCreator(
            $query->creatorId,
            $query->limit,
            $query->offset
        );

        $circleDTOs = array_map(
            fn($circle) => new CircleDTO(
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

        return new CircleListDTO(
            circles: $circleDTOs,
            total: count($circleDTOs),
            limit: $query->limit,
            offset: $query->offset
        );
    }
}
