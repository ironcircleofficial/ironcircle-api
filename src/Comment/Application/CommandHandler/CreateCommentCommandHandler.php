<?php

declare(strict_types=1);

namespace App\Comment\Application\CommandHandler;

use App\Comment\Application\Command\CreateCommentCommand;
use App\Comment\Domain\Exception\UnauthorizedCommentAccessException;
use App\Comment\Domain\Model\Comment;
use App\Comment\Domain\Repository\CommentRepositoryInterface;
use App\Post\Domain\Exception\PostNotFoundException;
use App\Post\Domain\Repository\PostRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreateCommentCommandHandler
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
        private PostRepositoryInterface $postRepository
    ) {
    }

    public function __invoke(CreateCommentCommand $command): void
    {
        $post = $this->postRepository->findById($command->postId);

        if ($post === null) {
            throw PostNotFoundException::withId($command->postId);
        }

        if ($command->parentCommentId !== null) {
            $parentComment = $this->commentRepository->findById($command->parentCommentId);

            if ($parentComment === null) {
                throw new \InvalidArgumentException(sprintf('Parent comment with ID "%s" was not found', $command->parentCommentId));
            }

            if ($parentComment->isReply()) {
                throw UnauthorizedCommentAccessException::replyToReply();
            }
        }

        $comment = new Comment(
            postId: $command->postId,
            authorId: $command->authorId,
            content: $command->content,
            parentCommentId: $command->parentCommentId
        );

        $this->commentRepository->save($comment);
    }
}
