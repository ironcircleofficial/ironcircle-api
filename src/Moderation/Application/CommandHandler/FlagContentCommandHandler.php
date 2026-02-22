<?php

declare(strict_types=1);

namespace App\Moderation\Application\CommandHandler;

use App\Comment\Domain\Exception\CommentNotFoundException;
use App\Comment\Domain\Repository\CommentRepositoryInterface;
use App\Moderation\Application\Command\FlagContentCommand;
use App\Moderation\Domain\Exception\DuplicateFlagException;
use App\Moderation\Domain\Model\Flag;
use App\Moderation\Domain\Repository\FlagRepositoryInterface;
use App\Post\Domain\Exception\PostNotFoundException;
use App\Post\Domain\Repository\PostRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class FlagContentCommandHandler
{
    public function __construct(
        private FlagRepositoryInterface $flagRepository,
        private PostRepositoryInterface $postRepository,
        private CommentRepositoryInterface $commentRepository
    ) {
    }

    public function __invoke(FlagContentCommand $command): void
    {
        $this->assertTargetExists($command->targetType, $command->targetId);

        $existing = $this->flagRepository->findByReporterAndTarget(
            $command->reporterId,
            $command->targetType,
            $command->targetId
        );

        if ($existing !== null) {
            throw DuplicateFlagException::forTarget($command->targetType, $command->targetId);
        }

        $flag = new Flag(
            targetType: $command->targetType,
            targetId: $command->targetId,
            reporterId: $command->reporterId,
            reason: $command->reason
        );

        $this->flagRepository->save($flag);
    }

    private function assertTargetExists(string $targetType, string $targetId): void
    {
        if ($targetType === 'post') {
            $post = $this->postRepository->findById($targetId);

            if ($post === null) {
                throw PostNotFoundException::withId($targetId);
            }

            return;
        }

        $comment = $this->commentRepository->findById($targetId);

        if ($comment === null) {
            throw CommentNotFoundException::withId($targetId);
        }
    }
}
