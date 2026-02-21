<?php

declare(strict_types=1);

namespace App\Post\Application\QueryHandler;

use App\Post\Application\DTO\PostAttachmentDTO;
use App\Post\Application\Query\ListPostAttachmentsByPostQuery;
use App\Post\Domain\Exception\PostNotFoundException;
use App\Post\Domain\Repository\PostAttachmentRepositoryInterface;
use App\Post\Domain\Repository\PostRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListPostAttachmentsByPostQueryHandler
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private PostAttachmentRepositoryInterface $attachmentRepository
    ) {
    }

    /**
     * @return array<PostAttachmentDTO>
     */
    public function __invoke(ListPostAttachmentsByPostQuery $query): array
    {
        $post = $this->postRepository->findById($query->postId);

        if ($post === null) {
            throw PostNotFoundException::withId($query->postId);
        }

        $attachments = $this->attachmentRepository->findByPostId($query->postId);

        return array_map(
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
    }
}
