<?php

declare(strict_types=1);

namespace App\Post\Application\QueryHandler;

use App\Post\Application\DTO\PostDTO;
use App\Post\Application\Query\GetPostByIdQuery;
use App\Post\Domain\Exception\PostNotFoundException;
use App\Post\Domain\Repository\PostRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetPostByIdQueryHandler
{
    public function __construct(
        private PostRepositoryInterface $postRepository
    ) {
    }

    public function __invoke(GetPostByIdQuery $query): PostDTO
    {
        $post = $this->postRepository->findById($query->id);

        if ($post === null) {
            throw PostNotFoundException::withId($query->id);
        }

        return new PostDTO(
            id: $post->getId(),
            circleId: $post->getCircleId(),
            authorId: $post->getAuthorId(),
            title: $post->getTitle(),
            content: $post->getContent(),
            imageUrls: $post->getImageUrls(),
            aiSummaryEnabled: $post->isAiSummaryEnabled(),
            createdAt: $post->getCreatedAt(),
            updatedAt: $post->getUpdatedAt()
        );
    }
}
