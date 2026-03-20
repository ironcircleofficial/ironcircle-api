<?php

declare(strict_types=1);

namespace App\Circle\Application\QueryHandler;

use App\Circle\Application\DTO\CircleDTO;
use App\Circle\Application\DTO\CircleListDTO;
use App\Circle\Application\Query\ListCirclesByCreatorQuery;
use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\User\Application\DTO\UserInlineDTO;
use App\User\Domain\Exception\UserNotFoundException;
use App\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListCirclesByCreatorQueryHandler
{
    public function __construct(
        private CircleRepositoryInterface $circleRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(ListCirclesByCreatorQuery $query): CircleListDTO
    {
        $circles = $this->circleRepository->findByCreator(
            $query->creatorId,
            $query->limit,
            $query->offset
        );

        $creatorIds = array_values(array_unique(array_map(fn($c) => $c->getCreatorId(), $circles)));
        $usersById = $this->userRepository->findByIds($creatorIds);

        $circleDTOs = array_map(
            function ($circle) use ($usersById) {
                $creator = $usersById[$circle->getCreatorId()] ?? null;

                if ($creator === null) {
                    throw UserNotFoundException::withId($circle->getCreatorId());
                }

                return new CircleDTO(
                    id: $circle->getId(),
                    name: $circle->getName(),
                    slug: $circle->getSlug(),
                    description: $circle->getDescription(),
                    visibility: $circle->getVisibility(),
                    creator: new UserInlineDTO($creator->getId(), $creator->getUsername()),
                    moderatorIds: $circle->getModeratorIds(),
                    createdAt: $circle->getCreatedAt(),
                    updatedAt: $circle->getUpdatedAt()
                );
            },
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
