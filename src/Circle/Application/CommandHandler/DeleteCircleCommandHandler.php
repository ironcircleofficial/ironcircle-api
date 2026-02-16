<?php

declare(strict_types=1);

namespace App\Circle\Application\CommandHandler;

use App\Circle\Application\Command\DeleteCircleCommand;
use App\Circle\Domain\Exception\CircleNotFoundException;
use App\Circle\Domain\Exception\UnauthorizedCircleAccessException;
use App\Circle\Domain\Repository\CircleRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteCircleCommandHandler
{
    public function __construct(
        private CircleRepositoryInterface $circleRepository
    ) {
    }

    public function __invoke(DeleteCircleCommand $command): void
    {
        $circle = $this->circleRepository->findById($command->circleId);

        if ($circle === null) {
            throw CircleNotFoundException::withId($command->circleId);
        }

        if (!$circle->isCreator($command->deletedBy)) {
            throw UnauthorizedCircleAccessException::notCreator();
        }

        $this->circleRepository->delete($circle);
    }
}
