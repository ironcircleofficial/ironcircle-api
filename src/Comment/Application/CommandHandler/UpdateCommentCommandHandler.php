<?php

declare(strict_types=1);

namespace App\Comment\Application\CommandHandler;

use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Comment\Application\Command\UpdateCommentCommand;
use App\Comment\Domain\Exception\CommentNotFoundException;
use App\Comment\Domain\Exception\UnauthorizedCommentAccessException;
use App\Comment\Domain\Repository\CommentRepositoryInterface;
use App\Post\Domain\Repository\PostRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateCommentCommandHandler
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
        private PostRepositoryInterface $postRepository,
        private CircleRepositoryInterface $circleRepository
    ) {
    }

    public function __invoke(UpdateCommentCommand $command): void
    {
        $comment = $this->commentRepository->findById($command->commentId);

        if ($comment === null) {
            throw CommentNotFoundException::withId($command->commentId);
        }

        if (!$comment->isOwnedBy($command->updatedBy)) {
            $post = $this->postRepository->findById($comment->getPostId());
            $circle = $post !== null ? $this->circleRepository->findById($post->getCircleId()) : null;

            $isModeratorOrCreator = $circle !== null
                && ($circle->isModerator($command->updatedBy) || $circle->isCreator($command->updatedBy));

            if (!$isModeratorOrCreator) {
                throw UnauthorizedCommentAccessException::notAuthor();
            }
        }

        $comment->updateContent($command->content);
        $this->commentRepository->save($comment);
    }
}
