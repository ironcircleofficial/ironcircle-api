<?php

declare(strict_types=1);

namespace App\Circle\Application\QueryHandler;

use App\Circle\Application\DTO\CircleDTO;
use App\Circle\Application\Query\GetCircleBySlugQuery;
use App\Circle\Domain\Exception\CircleNotFoundException;
use App\Circle\Domain\Repository\CircleRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetCircleBySlugQueryHandler
{
    public function __construct(
        private CircleRepositoryInterface $circleRepository
    ) {
    }

    public function __invoke(GetCircleBySlugQuery $query): CircleDTO
    {
        $circle = $this->circleRepository->findBySlug($query->slug);

        if ($circle === null) {
            throw CircleNotFoundException::withSlug($query->slug);
        }

        return new CircleDTO(
            id: $circle->getId(),
            name: $circle->getName(),
            slug: $circle->getSlug(),
            description: $circle->getDescription(),
            visibility: $circle->getVisibility(),
            creatorId: $circle->getCreatorId(),
            moderatorIds: $circle->getModeratorIds(),
            createdAt: $circle->getCreatedAt(),
            updatedAt: $circle->getUpdatedAt()
        );
    }
}
