<?php

declare(strict_types=1);

namespace App\Comment\Application\QueryHandler;

use App\Comment\Application\DTO\CommentDTO;
use App\Comment\Application\DTO\CommentListDTO;
use App\Comment\Application\Query\ListCommentsByPostQuery;
use App\Comment\Domain\Repository\CommentRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListCommentsByPostQueryHandler
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository
    ) {
    }

    public function __invoke(ListCommentsByPostQuery $query): CommentListDTO
    {
        $comments = $this->commentRepository->findByPostId($query->postId, $query->limit, $query->offset);
        $total = $this->commentRepository->countByPostId($query->postId);

        $commentDTOs = array_map(
            fn($comment) => new CommentDTO(
                id: $comment->getId(),
                postId: $comment->getPostId(),
                authorId: $comment->getAuthorId(),
                parentCommentId: $comment->getParentCommentId(),
                content: $comment->getContent(),
                createdAt: $comment->getCreatedAt(),
                updatedAt: $comment->getUpdatedAt()
            ),
            $comments
        );

        return new CommentListDTO(
            comments: $commentDTOs,
            total: $total,
            limit: $query->limit,
            offset: $query->offset
        );
    }
}
