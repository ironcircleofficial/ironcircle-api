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

    public function findByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $users = $this->repository->createQueryBuilder()
            ->field('id')->in($ids)
            ->getQuery()
            ->execute()
            ->toArray();

        $map = [];
        foreach ($users as $user) {
            $map[$user->getId()] = $user;
        }

        return $map;
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

    public function searchByUsername(string $query, int $limit = 20, int $offset = 0): array
    {
        $regex = new \MongoDB\BSON\Regex(preg_quote($query, '/'), 'i');

        return $this->repository->createQueryBuilder()
            ->field('username')->equals($regex)
            ->sort('username', 'ASC')
            ->limit($limit)
            ->skip($offset)
            ->getQuery()
            ->execute()
            ->toArray();
    }

    public function countSearchByUsername(string $query): int
    {
        $regex = new \MongoDB\BSON\Regex(preg_quote($query, '/'), 'i');

        return (int) $this->repository->createQueryBuilder()
            ->field('username')->equals($regex)
            ->count()
            ->getQuery()
            ->execute();
    }

    public function findAll(int $limit = 20, int $offset = 0): array
    {
        return $this->repository->findBy([], ['createdAt' => 'DESC'], $limit, $offset);
    }

    public function countAll(): int
    {
        return (int) $this->repository->createQueryBuilder()
            ->count()
            ->getQuery()
            ->execute();
    }

    public function delete(User $user): void
    {
        $this->documentManager->remove($user);
        $this->documentManager->flush();
    }
}
