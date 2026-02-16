<?php

declare(strict_types=1);

namespace App\Circle\Application\CommandHandler;

use App\Circle\Application\Command\UpdateCircleCommand;
use App\Circle\Domain\Exception\CircleNotFoundException;
use App\Circle\Domain\Exception\UnauthorizedCircleAccessException;
use App\Circle\Domain\Repository\CircleRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateCircleCommandHandler
{
    public function __construct(
        private CircleRepositoryInterface $circleRepository
    ) {
    }

    public function __invoke(UpdateCircleCommand $command): void
    {
        $circle = $this->circleRepository->findById($command->circleId);

        if ($circle === null) {
            throw CircleNotFoundException::withId($command->circleId);
        }

        if (!$circle->isCreator($command->updatedBy) && !$circle->isModerator($command->updatedBy)) {
            throw UnauthorizedCircleAccessException::notModerator();
        }

        $circle->updateDetails($command->name, $command->description);

        $this->circleRepository->save($circle);
    }
}
