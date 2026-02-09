<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Security;

use App\User\Domain\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

final class UserProvider implements UserProviderInterface, PasswordUpgraderInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        $user = $this->userRepository->findByUsername($identifier);

        if ($user === null) {
            throw new UserNotFoundException(sprintf('User "%s" not found', $identifier));
        }

        return new SecurityUser(
            id: $user->getId(),
            username: $user->getUsername(),
            password: $user->getPassword(),
            roles: $user->getRoles()
        );
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof SecurityUser) {
            throw new \InvalidArgumentException(sprintf('Expected instance of %s, got %s', SecurityUser::class, get_class($user)));
        }

        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    public function supportsClass(string $class): bool
    {
        return SecurityUser::class === $class;
    }

    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
    }
}
