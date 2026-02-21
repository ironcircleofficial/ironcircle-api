<?php

declare(strict_types=1);

namespace App\Post\Application\QueryHandler;

use App\Post\Application\DTO\PostAttachmentDTO;
use App\Post\Application\DTO\PostDTO;
use App\Post\Application\DTO\PostListDTO;
use App\Post\Application\Query\ListPostsByCircleQuery;
use App\Post\Domain\Repository\PostAttachmentRepositoryInterface;
use App\Post\Domain\Repository\PostRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListPostsByCircleQueryHandler
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private PostAttachmentRepositoryInterface $attachmentRepository
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
            $attachmentsByPost[$attachment->getPostId()][] = new PostAttachmentDTO(
                id: $attachment->getId(),
                postId: $attachment->getPostId(),
                authorId: $attachment->getAuthorId(),
                originalFilename: $attachment->getOriginalFilename(),
                storedFilename: $attachment->getStoredFilename(),
                mimeType: $attachment->getMimeType(),
                size: $attachment->getSize(),
                uploadedAt: $attachment->getUploadedAt()
            );
        }

        $postDTOs = array_map(
            fn($post) => new PostDTO(
                id: $post->getId(),
                circleId: $post->getCircleId(),
                authorId: $post->getAuthorId(),
                title: $post->getTitle(),
                content: $post->getContent(),
                aiSummaryEnabled: $post->isAiSummaryEnabled(),
                createdAt: $post->getCreatedAt(),
                updatedAt: $post->getUpdatedAt(),
                attachments: $attachmentsByPost[$post->getId()] ?? []
            ),
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
