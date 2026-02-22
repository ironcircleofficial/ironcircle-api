<?php

declare(strict_types=1);

namespace App\Moderation\Application\CommandHandler;

use App\Moderation\Application\Command\DismissFlagCommand;
use App\Moderation\Domain\Exception\FlagAlreadyResolvedException;
use App\Moderation\Domain\Exception\FlagNotFoundException;
use App\Moderation\Domain\Repository\FlagRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DismissFlagCommandHandler
{
    public function __construct(
        private FlagRepositoryInterface $flagRepository
    ) {
    }

    public function __invoke(DismissFlagCommand $command): void
    {
        $flag = $this->flagRepository->findById($command->flagId);

        if ($flag === null) {
            throw FlagNotFoundException::withId($command->flagId);
        }

        if ($flag->isResolved()) {
            throw FlagAlreadyResolvedException::withId($command->flagId);
        }

        $flag->reject($command->moderatorId);

        $this->flagRepository->save($flag);
    }
}
