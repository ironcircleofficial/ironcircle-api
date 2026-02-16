<?php

declare(strict_types=1);

namespace App\Circle\Domain\Repository;

use App\Circle\Domain\Model\Circle;

interface CircleRepositoryInterface
{
    public function save(Circle $circle): void;

    public function findById(string $id): ?Circle;

    public function findBySlug(string $slug): ?Circle;

    public function existsBySlug(string $slug): bool;

    public function existsByName(string $name): bool;

    /**
     * @param int<1, max> $limit
     * @param int<0, max> $offset
     * @return array<Circle>
     */
    public function findAll(int $limit = 20, int $offset = 0): array;

    /**
     * @param int<1, max> $limit
     * @param int<0, max> $offset
     * @return array<Circle>
     */
    public function findPublicCircles(int $limit = 20, int $offset = 0): array;

    /**
     * @param int<1, max> $limit
     * @param int<0, max> $offset
     * @return array<Circle>
     */
    public function findByCreator(string $creatorId, int $limit = 20, int $offset = 0): array;

    public function count(): int;

    public function countPublic(): int;

    public function delete(Circle $circle): void;
}
