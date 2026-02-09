<?php

declare(strict_types=1);

namespace App\User\Application\CommandHandler;

use App\User\Application\Command\RegisterUserCommand;
use App\User\Domain\Exception\UserAlreadyExistsException;
use App\User\Domain\Model\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Infrastructure\Security\SecurityUser;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
final readonly class RegisterUserCommandHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function __invoke(RegisterUserCommand $command): void
    {
        if ($this->userRepository->existsByUsername($command->username)) {
            throw UserAlreadyExistsException::withUsername($command->username);
        }

        if ($this->userRepository->existsByEmail($command->email)) {
            throw UserAlreadyExistsException::withEmail($command->email);
        }

        $tempUser = new SecurityUser(
            id: '',
            username: $command->username,
            password: '',
            roles: ['ROLE_MEMBER']
        );

        $hashedPassword = $this->passwordHasher->hashPassword($tempUser, $command->password);

        $user = new User(
            username: $command->username,
            email: $command->email,
            password: $hashedPassword,
            roles: ['ROLE_MEMBER']
        );

        $this->userRepository->save($user);
    }
}
