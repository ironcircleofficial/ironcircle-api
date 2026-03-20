<?php

declare(strict_types=1);

namespace App\Circle\Application\QueryHandler;

use App\Circle\Application\DTO\CircleDTO;
use App\Circle\Application\Query\GetCircleByIdQuery;
use App\Circle\Domain\Exception\CircleNotFoundException;
use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\User\Application\DTO\UserInlineDTO;
use App\User\Domain\Exception\UserNotFoundException;
use App\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetCircleByIdQueryHandler
{
    public function __construct(
        private CircleRepositoryInterface $circleRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(GetCircleByIdQuery $query): CircleDTO
    {
        $circle = $this->circleRepository->findById($query->id);

        if ($circle === null) {
            throw CircleNotFoundException::withId($query->id);
        }

        $creator = $this->userRepository->findById($circle->getCreatorId());

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
    }
}
