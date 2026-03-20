<?php

declare(strict_types=1);

namespace App\Post\Application\QueryHandler;

use App\Post\Application\DTO\PostAttachmentDTO;
use App\Post\Application\DTO\PostDTO;
use App\Post\Application\Query\GetPostByIdQuery;
use App\Post\Domain\Exception\PostNotFoundException;
use App\Post\Domain\Repository\PostAttachmentRepositoryInterface;
use App\Post\Domain\Repository\PostRepositoryInterface;
use App\User\Application\DTO\UserInlineDTO;
use App\User\Domain\Exception\UserNotFoundException;
use App\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetPostByIdQueryHandler
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private PostAttachmentRepositoryInterface $attachmentRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(GetPostByIdQuery $query): PostDTO
    {
        $post = $this->postRepository->findById($query->id);

        if ($post === null) {
            throw PostNotFoundException::withId($query->id);
        }

        $author = $this->userRepository->findById($post->getAuthorId());

        if ($author === null) {
            throw UserNotFoundException::withId($post->getAuthorId());
        }

        $authorInline = new UserInlineDTO($author->getId(), $author->getUsername());

        $attachments = $this->attachmentRepository->findByPostId($post->getId());
        $attachmentDTOs = array_map(
            function ($attachment) {
                $attachmentAuthor = $this->userRepository->findById($attachment->getAuthorId());

                if ($attachmentAuthor === null) {
                    throw UserNotFoundException::withId($attachment->getAuthorId());
                }

                return new PostAttachmentDTO(
                    id: $attachment->getId(),
                    postId: $attachment->getPostId(),
                    author: new UserInlineDTO($attachmentAuthor->getId(), $attachmentAuthor->getUsername()),
                    originalFilename: $attachment->getOriginalFilename(),
                    storedFilename: $attachment->getStoredFilename(),
                    mimeType: $attachment->getMimeType(),
                    size: $attachment->getSize(),
                    uploadedAt: $attachment->getUploadedAt()
                );
            },
            $attachments
        );

        return new PostDTO(
            id: $post->getId(),
            circleId: $post->getCircleId(),
            author: $authorInline,
            title: $post->getTitle(),
            content: $post->getContent(),
            aiSummaryEnabled: $post->isAiSummaryEnabled(),
            createdAt: $post->getCreatedAt(),
            updatedAt: $post->getUpdatedAt(),
            attachments: $attachmentDTOs
        );
    }
}
