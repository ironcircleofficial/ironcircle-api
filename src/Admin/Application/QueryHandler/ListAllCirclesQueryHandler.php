<?php

declare(strict_types=1);

namespace App\Admin\Application\QueryHandler;

use App\Admin\Application\DTO\AdminCircleListDTO;
use App\Admin\Application\Query\ListAllCirclesQuery;
use App\Circle\Application\DTO\CircleDTO;
use App\Circle\Domain\Repository\CircleRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListAllCirclesQueryHandler
{
    public function __construct(
        private CircleRepositoryInterface $circleRepository
    ) {
    }

    public function __invoke(ListAllCirclesQuery $query): AdminCircleListDTO
    {
        $circles = $this->circleRepository->findAll($query->limit, $query->offset);
        $total = $this->circleRepository->count();

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

        return new AdminCircleListDTO(
            circles: $circleDTOs,
            total: $total,
            limit: $query->limit,
            offset: $query->offset
        );
    }
}
