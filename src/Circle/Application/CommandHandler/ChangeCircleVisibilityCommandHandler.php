<?php

declare(strict_types=1);

namespace App\Circle\Application\CommandHandler;

use App\Circle\Application\Command\ChangeCircleVisibilityCommand;
use App\Circle\Domain\Exception\CircleNotFoundException;
use App\Circle\Domain\Exception\UnauthorizedCircleAccessException;
use App\Circle\Domain\Repository\CircleRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ChangeCircleVisibilityCommandHandler
{
    public function __construct(
        private CircleRepositoryInterface $circleRepository
    ) {
    }

    public function __invoke(ChangeCircleVisibilityCommand $command): void
    {
        $circle = $this->circleRepository->findById($command->circleId);

        if ($circle === null) {
            throw CircleNotFoundException::withId($command->circleId);
        }

        if (!$circle->isCreator($command->updatedBy)) {
            throw UnauthorizedCircleAccessException::notCreator();
        }

        $circle->changeVisibility($command->visibility);

        $this->circleRepository->save($circle);
    }
}
