<?php

declare(strict_types=1);

namespace App\Post\Application\QueryHandler;

use App\Post\Application\DTO\PostAttachmentDTO;
use App\Post\Application\DTO\PostDTO;
use App\Post\Application\DTO\PostListDTO;
use App\Post\Application\Query\ListPostsByCircleQuery;
use App\Post\Domain\Repository\PostAttachmentRepositoryInterface;
use App\Post\Domain\Repository\PostRepositoryInterface;
use App\User\Application\DTO\UserInlineDTO;
use App\User\Domain\Exception\UserNotFoundException;
use App\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListPostsByCircleQueryHandler
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private PostAttachmentRepositoryInterface $attachmentRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(ListPostsByCircleQuery $query): PostListDTO
    {
        $posts = $this->postRepository->findByCircle($query->circleId, $query->limit, $query->offset);
        $total = $this->postRepository->countByCircle($query->circleId);

        $postIds = array_map(fn($post) => $post->getId(), $posts);
        $allAttachments = $this->attachmentRepository->findByPostIds($postIds);

        $attachmentsByPost = [];
        foreach ($allAttachments as $attachment) {
            $attachmentAuthor = $this->userRepository->findById($attachment->getAuthorId());

            if ($attachmentAuthor === null) {
                throw UserNotFoundException::withId($attachment->getAuthorId());
            }

            $attachmentsByPost[$attachment->getPostId()][] = new PostAttachmentDTO(
                id: $attachment->getId(),
                postId: $attachment->getPostId(),
                author: new UserInlineDTO($attachmentAuthor->getId(), $attachmentAuthor->getUsername()),
                originalFilename: $attachment->getOriginalFilename(),
                storedFilename: $attachment->getStoredFilename(),
                mimeType: $attachment->getMimeType(),
                size: $attachment->getSize(),
                uploadedAt: $attachment->getUploadedAt()
            );
        }

        $postDTOs = array_map(
            function ($post) use ($attachmentsByPost) {
                $author = $this->userRepository->findById($post->getAuthorId());

                if ($author === null) {
                    throw UserNotFoundException::withId($post->getAuthorId());
                }

                return new PostDTO(
                    id: $post->getId(),
                    circleId: $post->getCircleId(),
                    author: new UserInlineDTO($author->getId(), $author->getUsername()),
                    title: $post->getTitle(),
                    content: $post->getContent(),
                    aiSummaryEnabled: $post->isAiSummaryEnabled(),
                    createdAt: $post->getCreatedAt(),
                    updatedAt: $post->getUpdatedAt(),
                    attachments: $attachmentsByPost[$post->getId()] ?? []
                );
            },
            $posts
        );

        return new PostListDTO(
            posts: $postDTOs,
            total: $total,
            limit: $query->limit,
            offset: $query->offset
        );
    }
}
