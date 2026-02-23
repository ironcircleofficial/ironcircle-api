<?php

declare(strict_types=1);

namespace App\Search\Application\QueryHandler;

use App\Search\Application\DTO\SearchUserDTO;
use App\Search\Application\DTO\SearchUsersResultDTO;
use App\Search\Application\Query\SearchUsersQuery;
use App\User\Domain\Model\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SearchUsersQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(SearchUsersQuery $query): SearchUsersResultDTO
    {
        $users = $this->userRepository->searchByUsername(
            $query->query,
            $query->limit,
            $query->offset
        );

        $total = $this->userRepository->countSearchByUsername($query->query);

        $userDTOs = array_map(
            fn(User $user) => new SearchUserDTO(
                id: $user->getId(),
                username: $user->getUsername(),
                roles: $user->getRoles(),
                createdAt: $user->getCreatedAt()
            ),
            $users
        );

        return new SearchUsersResultDTO(
            query: $query->query,
            users: $userDTOs,
            total: $total,
            limit: $query->limit,
            offset: $query->offset
        );
    }
}
