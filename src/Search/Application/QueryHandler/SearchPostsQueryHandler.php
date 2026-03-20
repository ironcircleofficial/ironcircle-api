<?php

declare(strict_types=1);

namespace App\Search\Application\QueryHandler;

use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Post\Application\DTO\PostDTO;
use App\Post\Domain\Model\Post;
use App\Post\Domain\Repository\PostRepositoryInterface;
use App\Search\Application\DTO\SearchPostsResultDTO;
use App\Search\Application\Query\SearchPostsQuery;
use App\User\Application\DTO\UserInlineDTO;
use App\User\Domain\Exception\UserNotFoundException;
use App\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SearchPostsQueryHandler
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private CircleRepositoryInterface $circleRepository,
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(SearchPostsQuery $query): SearchPostsResultDTO
    {
        $accessibleCircleIds = $this->resolveAccessibleCircleIds($query->userId);

        $posts = $this->postRepository->searchByQuery(
            $query->query,
            $accessibleCircleIds,
            $query->limit,
            $query->offset
        );

        $total = $this->postRepository->countSearchByQuery($query->query, $accessibleCircleIds);

        $postDTOs = array_map(
            function (Post $post) {
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
                    attachments: []
                );
            },
            $posts
        );

        return new SearchPostsResultDTO(
            query: $query->query,
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
