<?php

declare(strict_types=1);

namespace App\Circle\Application\QueryHandler;

use App\Circle\Application\DTO\CircleDTO;
use App\Circle\Application\DTO\CircleListDTO;
use App\Circle\Application\Query\ListCirclesQuery;
use App\Circle\Domain\Repository\CircleRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListCirclesQueryHandler
{
    public function __construct(
        private CircleRepositoryInterface $circleRepository
    ) {
    }

    public function __invoke(ListCirclesQuery $query): CircleListDTO
    {
        $circles = $query->publicOnly
            ? $this->circleRepository->findPublicCircles($query->limit, $query->offset)
            : $this->circleRepository->findAll($query->limit, $query->offset);

        $total = $query->publicOnly
            ? $this->circleRepository->countPublic()
            : $this->circleRepository->count();

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
            total: $total,
            limit: $query->limit,
            offset: $query->offset
        );
    }
}
