<?php

declare(strict_types=1);

namespace App\Admin\Application\CommandHandler;

use App\Admin\Application\Command\UpdateUserRolesCommand;
use App\Admin\Domain\Exception\InvalidRoleException;
use App\Admin\Domain\Exception\UserNotFoundException;
use App\User\Domain\Model\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdateUserRolesCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(UpdateUserRolesCommand $command): void
    {
        $user = $this->userRepository->findById($command->userId);

        if ($user === null) {
            throw UserNotFoundException::withId($command->userId);
        }

        foreach ($command->roles as $role) {
            if (!in_array($role, User::VALID_ROLES, true)) {
                throw InvalidRoleException::withRole($role);
            }
        }

        $user->updateRoles($command->roles);
        $this->userRepository->save($user);
    }
}
