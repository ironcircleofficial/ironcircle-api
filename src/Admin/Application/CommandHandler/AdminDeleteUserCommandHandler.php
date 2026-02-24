<?php

declare(strict_types=1);

namespace App\Admin\Application\CommandHandler;

use App\Admin\Application\Command\AdminDeleteUserCommand;
use App\Admin\Domain\Exception\CannotDeleteAdminException;
use App\Admin\Domain\Exception\UserNotFoundException;
use App\User\Domain\Model\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class AdminDeleteUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function __invoke(AdminDeleteUserCommand $command): void
    {
        $user = $this->userRepository->findById($command->userId);

        if ($user === null) {
            throw UserNotFoundException::withId($command->userId);
        }

        if ($user->hasRole(User::ROLE_ADMIN)) {
            throw CannotDeleteAdminException::create();
        }

        $this->userRepository->delete($user);
    }
}
