<?php

declare(strict_types=1);

namespace App\Vote\Application\CommandHandler;

use App\Vote\Application\Command\RetractVoteCommand;
use App\Vote\Domain\Exception\VoteNotFoundException;
use App\Vote\Domain\Repository\VoteRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class RetractVoteCommandHandler
{
    public function __construct(
        private VoteRepositoryInterface $voteRepository
    ) {
    }

    public function __invoke(RetractVoteCommand $command): void
    {
        $vote = $this->voteRepository->findByUserAndTarget(
            $command->userId,
            $command->targetType,
            $command->targetId
        );

        if ($vote === null) {
            throw VoteNotFoundException::forTarget($command->targetType, $command->targetId);
        }

        $this->voteRepository->delete($vote);
    }
}
