<?php

declare(strict_types=1);

namespace App\Comment\Application\CommandHandler;

use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Comment\Application\Command\DeleteCommentCommand;
use App\Comment\Domain\Exception\CommentNotFoundException;
use App\Comment\Domain\Exception\UnauthorizedCommentAccessException;
use App\Comment\Domain\Repository\CommentRepositoryInterface;
use App\Post\Domain\Repository\PostRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeleteCommentCommandHandler
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
        private PostRepositoryInterface $postRepository,
        private CircleRepositoryInterface $circleRepository
    ) {
    }

    public function __invoke(DeleteCommentCommand $command): void
    {
        $comment = $this->commentRepository->findById($command->commentId);

        if ($comment === null) {
            throw CommentNotFoundException::withId($command->commentId);
        }

        if (!$comment->isOwnedBy($command->deletedBy)) {
            $post = $this->postRepository->findById($comment->getPostId());
            $circle = $post !== null ? $this->circleRepository->findById($post->getCircleId()) : null;

            $isModeratorOrCreator = $circle !== null
                && ($circle->isModerator($command->deletedBy) || $circle->isCreator($command->deletedBy));

            if (!$isModeratorOrCreator) {
                throw UnauthorizedCommentAccessException::notAuthor();
            }
        }

        $this->commentRepository->delete($comment);
    }
}
