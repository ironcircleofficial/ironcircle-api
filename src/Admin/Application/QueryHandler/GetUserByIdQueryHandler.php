<?php

declare(strict_types=1);

namespace App\Admin\Application\QueryHandler;

use App\Admin\Application\DTO\AdminUserDTO;
use App\Admin\Application\Query\GetUserByIdQuery;
use App\Admin\Domain\Exception\UserNotFoundException;
use App\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class GetUserByIdQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(GetUserByIdQuery $query): AdminUserDTO
    {
        $user = $this->userRepository->findById($query->userId);

        if ($user === null) {
            throw UserNotFoundException::withId($query->userId);
        }

        return new AdminUserDTO(
            id: $user->getId(),
            username: $user->getUsername(),
            email: $user->getEmail(),
            roles: $user->getRoles(),
            createdAt: $user->getCreatedAt(),
            updatedAt: $user->getUpdatedAt()
        );
    }
}
