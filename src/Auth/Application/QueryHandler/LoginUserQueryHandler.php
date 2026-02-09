<?php

declare(strict_types=1);

namespace App\Auth\Application\QueryHandler;

use App\Auth\Application\DTO\TokenDTO;
use App\Auth\Application\Query\LoginUserQuery;
use App\Auth\Domain\Exception\InvalidCredentialsException;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Infrastructure\Security\SecurityUser;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsMessageHandler]
final readonly class LoginUserQueryHandler
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private JWTTokenManagerInterface $jwtManager
    ) {
    }

    public function __invoke(LoginUserQuery $query): TokenDTO
    {
        $user = $this->userRepository->findByUsername($query->username);

        if ($user === null) {
            throw InvalidCredentialsException::create();
        }

        $securityUser = new SecurityUser(
            id: $user->getId(),
            username: $user->getUsername(),
            password: $user->getPassword(),
            roles: $user->getRoles()
        );

        $isPasswordValid = $this->passwordHasher->isPasswordValid($securityUser, $query->password);

        if (!$isPasswordValid) {
            throw InvalidCredentialsException::create();
        }

        $token = $this->jwtManager->create($securityUser);

        return new TokenDTO(
            token: $token,
            userId: $user->getId(),
            username: $user->getUsername()
        );
    }
}
