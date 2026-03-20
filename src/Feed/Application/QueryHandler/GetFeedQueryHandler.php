<?php

declare(strict_types=1);

namespace App\Feed\Application\QueryHandler;

use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Feed\Application\DTO\FeedDTO;
use App\Feed\Application\Query\GetFeedQuery;
use App\Post\Application\DTO\PostAttachmentDTO;
use App\Post\Application\DTO\PostDTO;
use App\Post\Domain\Repository\PostAttachmentRepositoryInterface;
use App\Post\Domain\Repository\PostRepositoryInterface;
use App\User\Application\DTO\UserInlineDTO;
use App\User\Domain\Exception\UserNotFoundException;
use App\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetFeedQueryHandler
{
    public function __construct(
        private CircleRepositoryInterface $circleRepository,
        private PostRepositoryInterface $postRepository,
        private PostAttachmentRepositoryInterface $attachmentRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(GetFeedQuery $query): FeedDTO
    {
        $circleIds = $this->resolveAccessibleCircleIds($query->userId);

        $posts = $this->postRepository->findByCircleIds($circleIds, $query->limit, $query->offset);
        $total = $this->postRepository->countByCircleIds($circleIds);

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

        return new FeedDTO(
            posts: $postDTOs,
            total: $total,
            limit: $query->limit,
            offset: $query->offset
        );
    }

    /**
     * @return array<string>
     */
    private function resolveAccessibleCircleIds(?string $userId): array
    {
        $publicIds = $this->circleRepository->findPublicCircleIds();

        if ($userId === null) {
            return $publicIds;
        }

        $userCircleIds = $this->circleRepository->findCircleIdsByCreatorOrModerator($userId);

        return array_values(array_unique(array_merge($publicIds, $userCircleIds)));
    }
}
