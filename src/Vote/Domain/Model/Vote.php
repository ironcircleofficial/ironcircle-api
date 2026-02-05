<?php

declare(strict_types=1);

namespace App\Vote\Domain\Model;

use DateTimeImmutable;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: 'votes')]
#[MongoDB\UniqueIndex(keys: ['userId' => 'asc', 'targetType' => 'asc', 'targetId' => 'asc'])]
#[MongoDB\Index(keys: ['targetId' => 'asc'])]
#[MongoDB\Index(keys: ['targetId' => 'asc', 'targetType' => 'asc'])]
class Vote
{
    #[MongoDB\Id]
    private string $id;

    #[MongoDB\Field(type: 'string')]
    private readonly string $userId;

    #[MongoDB\Field(type: 'string')]
    private readonly string $targetType;

    #[MongoDB\Field(type: 'string')]
    private readonly string $targetId;

    #[MongoDB\Field(type: 'int')]
    private int $value;

    #[MongoDB\Field(type: 'date_immutable')]
    private readonly DateTimeImmutable $createdAt;

    public function __construct(
        string $userId,
        string $targetType,
        string $targetId,
        int $value
    ) {
        $this->userId = $userId;
        $this->targetType = $targetType;
        $this->targetId = $targetId;
        $this->value = $value;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getTargetType(): string
    {
        return $this->targetType;
    }

    public function getTargetId(): string
    {
        return $this->targetId;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function changeValue(int $value): void
    {
        $this->value = $value;
    }

    public function isUpvote(): bool
    {
        return $this->value === 1;
    }

    public function isDownvote(): bool
    {
        return $this->value === -1;
    }

    public function isForPost(): bool
    {
        return $this->targetType === 'post';
    }

    public function isForComment(): bool
    {
        return $this->targetType === 'comment';
    }
}
