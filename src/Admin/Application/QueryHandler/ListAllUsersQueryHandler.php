<?php

declare(strict_types=1);

namespace App\Admin\Application\QueryHandler;

use App\Admin\Application\DTO\AdminUserDTO;
use App\Admin\Application\DTO\AdminUserListDTO;
use App\Admin\Application\Query\ListAllUsersQuery;
use App\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ListAllUsersQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(ListAllUsersQuery $query): AdminUserListDTO
    {
        $users = $this->userRepository->findAll($query->limit, $query->offset);
        $total = $this->userRepository->countAll();

        $userDTOs = array_map(
            fn($user) => new AdminUserDTO(
                id: $user->getId(),
                username: $user->getUsername(),
                email: $user->getEmail(),
                roles: $user->getRoles(),
                createdAt: $user->getCreatedAt(),
                updatedAt: $user->getUpdatedAt()
            ),
            $users
        );

        return new AdminUserListDTO(
            users: $userDTOs,
            total: $total,
            limit: $query->limit,
            offset: $query->offset
        );
    }
}
