<?php

declare(strict_types=1);

namespace App\Vote\Application\CommandHandler;

use App\Comment\Domain\Exception\CommentNotFoundException;
use App\Comment\Domain\Repository\CommentRepositoryInterface;
use App\Post\Domain\Exception\PostNotFoundException;
use App\Post\Domain\Repository\PostRepositoryInterface;
use App\Vote\Application\Command\CastVoteCommand;
use App\Vote\Domain\Exception\SelfVotingException;
use App\Vote\Domain\Model\Vote;
use App\Vote\Domain\Repository\VoteRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CastVoteCommandHandler
{
    public function __construct(
        private VoteRepositoryInterface $voteRepository,
        private PostRepositoryInterface $postRepository,
        private CommentRepositoryInterface $commentRepository
    ) {
    }

    public function __invoke(CastVoteCommand $command): void
    {
        $authorId = $this->resolveTargetAuthorId($command->targetType, $command->targetId);

        if ($authorId === $command->userId) {
            throw SelfVotingException::create();
        }

        $vote = $this->voteRepository->findByUserAndTarget(
            $command->userId,
            $command->targetType,
            $command->targetId
        );

        if ($vote !== null) {
            $vote->changeValue($command->value);
        } else {
            $vote = new Vote(
                userId: $command->userId,
                targetType: $command->targetType,
                targetId: $command->targetId,
                value: $command->value
            );
        }

        $this->voteRepository->save($vote);
    }

    private function resolveTargetAuthorId(string $targetType, string $targetId): string
    {
        if ($targetType === 'post') {
            $post = $this->postRepository->findById($targetId);

            if ($post === null) {
                throw PostNotFoundException::withId($targetId);
            }

            return $post->getAuthorId();
        }

        $comment = $this->commentRepository->findById($targetId);

        if ($comment === null) {
            throw CommentNotFoundException::withId($targetId);
        }

        return $comment->getAuthorId();
    }
}
