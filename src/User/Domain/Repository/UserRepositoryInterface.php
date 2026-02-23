<?php

declare(strict_types=1);

namespace App\User\Domain\Repository;

use App\User\Domain\Model\User;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function findById(string $id): ?User;

    public function findByUsername(string $username): ?User;

    public function findByEmail(string $email): ?User;

    public function existsByUsername(string $username): bool;

    public function existsByEmail(string $email): bool;

    /**
     * @return array<User>
     */
    public function searchByUsername(string $query, int $limit = 20, int $offset = 0): array;

    public function countSearchByUsername(string $query): int;
}
