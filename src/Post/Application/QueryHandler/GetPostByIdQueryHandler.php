<?php

declare(strict_types=1);

namespace App\Post\Application\QueryHandler;

use App\Post\Application\DTO\PostAttachmentDTO;
use App\Post\Application\DTO\PostDTO;
use App\Post\Application\Query\GetPostByIdQuery;
use App\Post\Domain\Exception\PostNotFoundException;
use App\Post\Domain\Repository\PostAttachmentRepositoryInterface;
use App\Post\Domain\Repository\PostRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetPostByIdQueryHandler
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private PostAttachmentRepositoryInterface $attachmentRepository
    ) {
    }

    public function __invoke(GetPostByIdQuery $query): PostDTO
    {
        $post = $this->postRepository->findById($query->id);

        if ($post === null) {
            throw PostNotFoundException::withId($query->id);
        }

        $attachments = $this->attachmentRepository->findByPostId($post->getId());
        $attachmentDTOs = array_map(
            fn($attachment) => new PostAttachmentDTO(
                id: $attachment->getId(),
                postId: $attachment->getPostId(),
                authorId: $attachment->getAuthorId(),
                originalFilename: $attachment->getOriginalFilename(),
                storedFilename: $attachment->getStoredFilename(),
                mimeType: $attachment->getMimeType(),
                size: $attachment->getSize(),
                uploadedAt: $attachment->getUploadedAt()
            ),
            $attachments
        );

        return new PostDTO(
            id: $post->getId(),
            circleId: $post->getCircleId(),
            authorId: $post->getAuthorId(),
            title: $post->getTitle(),
            content: $post->getContent(),
            aiSummaryEnabled: $post->isAiSummaryEnabled(),
            createdAt: $post->getCreatedAt(),
            updatedAt: $post->getUpdatedAt(),
            attachments: $attachmentDTOs
        );
    }
}
