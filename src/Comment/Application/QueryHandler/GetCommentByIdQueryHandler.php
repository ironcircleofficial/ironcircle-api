<?php

declare(strict_types=1);

namespace App\Comment\Application\QueryHandler;

use App\Comment\Application\DTO\CommentDTO;
use App\Comment\Application\Query\GetCommentByIdQuery;
use App\Comment\Domain\Exception\CommentNotFoundException;
use App\Comment\Domain\Repository\CommentRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetCommentByIdQueryHandler
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository
    ) {
    }

    public function __invoke(GetCommentByIdQuery $query): CommentDTO
    {
        $comment = $this->commentRepository->findById($query->id);

        if ($comment === null) {
            throw CommentNotFoundException::withId($query->id);
        }

        return new CommentDTO(
            id: $comment->getId(),
            postId: $comment->getPostId(),
            authorId: $comment->getAuthorId(),
            parentCommentId: $comment->getParentCommentId(),
            content: $comment->getContent(),
            createdAt: $comment->getCreatedAt(),
            updatedAt: $comment->getUpdatedAt()
        );
    }
}
