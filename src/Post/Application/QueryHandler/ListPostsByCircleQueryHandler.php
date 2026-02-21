<?php

declare(strict_types=1);

namespace App\Post\Application\QueryHandler;

use App\Post\Application\DTO\PostDTO;
use App\Post\Application\DTO\PostListDTO;
use App\Post\Application\Query\ListPostsByCircleQuery;
use App\Post\Domain\Repository\PostRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListPostsByCircleQueryHandler
{
    public function __construct(
        private PostRepositoryInterface $postRepository
    ) {
    }

    public function __invoke(ListPostsByCircleQuery $query): PostListDTO
    {
        $posts = $this->postRepository->findByCircle($query->circleId, $query->limit, $query->offset);
        $total = $this->postRepository->countByCircle($query->circleId);

        $postDTOs = array_map(
            fn($post) => new PostDTO(
                id: $post->getId(),
                circleId: $post->getCircleId(),
                authorId: $post->getAuthorId(),
                title: $post->getTitle(),
                content: $post->getContent(),
                imageUrls: $post->getImageUrls(),
                aiSummaryEnabled: $post->isAiSummaryEnabled(),
                createdAt: $post->getCreatedAt(),
                updatedAt: $post->getUpdatedAt()
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
