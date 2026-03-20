<?php

declare(strict_types=1);

namespace App\Comment\Application\QueryHandler;

use App\Comment\Application\DTO\CommentDTO;
use App\Comment\Application\DTO\CommentListDTO;
use App\Comment\Application\Query\ListCommentsByPostQuery;
use App\Comment\Domain\Repository\CommentRepositoryInterface;
use App\User\Application\DTO\UserInlineDTO;
use App\User\Domain\Exception\UserNotFoundException;
use App\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListCommentsByPostQueryHandler
{
    public function __construct(
        private CommentRepositoryInterface $commentRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(ListCommentsByPostQuery $query): CommentListDTO
    {
        $comments = $this->commentRepository->findByPostId($query->postId, $query->limit, $query->offset);
        $total = $this->commentRepository->countByPostId($query->postId);

        $authorIds = array_values(array_unique(array_map(fn($c) => $c->getAuthorId(), $comments)));
        $usersById = $this->userRepository->findByIds($authorIds);

        $commentDTOs = array_map(
            function ($comment) use ($usersById) {
                $author = $usersById[$comment->getAuthorId()] ?? null;

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
            },
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
