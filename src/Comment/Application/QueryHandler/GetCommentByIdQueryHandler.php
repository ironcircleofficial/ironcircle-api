<?php

declare(strict_types=1);

namespace App\Comment\Application\QueryHandler;

use App\Comment\Application\DTO\CommentDTO;
use App\Comment\Application\Query\GetCommentByIdQuery;
use App\Comment\Domain\Exception\CommentNotFoundException;
use App\Comment\Domain\Repository\CommentRepositoryInterface;
use App\User\Application\DTO\UserInlineDTO;
use App\User\Domain\Exception\UserNotFoundException;
use App\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetCommentByIdQueryHandler
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(GetCommentByIdQuery $query): CommentDTO
    {
        $comment = $this->commentRepository->findById($query->id);

        if ($comment === null) {
            throw CommentNotFoundException::withId($query->id);
        }

        $author = $this->userRepository->findById($comment->getAuthorId());

        if ($author === null) {
            throw UserNotFoundException::withId($comment->getAuthorId());
        }

        return new CommentDTO(
            id: $comment->getId(),
            postId: $comment->getPostId(),
            author: new UserInlineDTO($author->getId(), $author->getUsername()),
            parentCommentId: $comment->getParentCommentId(),
            content: $comment->getContent(),
            createdAt: $comment->getCreatedAt(),
            updatedAt: $comment->getUpdatedAt()
        );
    }
}
