<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Repository;

use App\User\Domain\Model\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Repository\DocumentRepository;

final class UserRepository implements UserRepositoryInterface
{
    private DocumentRepository $repository;

    public function __construct(
        private readonly DocumentManager $documentManager
    ) {
        $this->repository = $this->documentManager->getRepository(User::class);
    }

    public function save(User $user): void
    {
        $this->documentManager->persist($user);
        $this->documentManager->flush();
    }

    public function findById(string $id): ?User
    {
        return $this->repository->find($id);
    }

    public function findByUsername(string $username): ?User
    {
        return $this->repository->findOneBy(['username' => $username]);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->repository->findOneBy(['email' => $email]);
    }

    public function existsByUsername(string $username): bool
    {
        return $this->repository->findOneBy(['username' => $username]) !== null;
    }

    public function existsByEmail(string $email): bool
    {
        return $this->repository->findOneBy(['email' => $email]) !== null;
    }
}
