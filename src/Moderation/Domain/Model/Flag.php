<?php

declare(strict_types=1);

namespace App\Moderation\Domain\Model;

use DateTimeImmutable;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: 'flags')]
#[MongoDB\Index(keys: ['targetId' => 'asc'])]
#[MongoDB\Index(keys: ['reporterId' => 'asc'])]
#[MongoDB\Index(keys: ['status' => 'asc'])]
#[MongoDB\Index(keys: ['status' => 'asc', 'createdAt' => 'asc'])]
class Flag
{
    #[MongoDB\Id]
    private string $id;

    #[MongoDB\Field(type: 'string')]
    private readonly string $targetType;

    #[MongoDB\Field(type: 'string')]
    private readonly string $targetId;

    #[MongoDB\Field(type: 'string')]
    private readonly string $reporterId;

    #[MongoDB\Field(type: 'string')]
    private readonly string $reason;

    #[MongoDB\Field(type: 'string')]
    private string $status;

    #[MongoDB\Field(type: 'string', nullable: true)]
    private ?string $resolvedById;

    #[MongoDB\Field(type: 'date_immutable', nullable: true)]
    private ?DateTimeImmutable $resolvedAt;

    #[MongoDB\Field(type: 'date_immutable')]
    private readonly DateTimeImmutable $createdAt;

    public function __construct(
        string $targetType,
        string $targetId,
        string $reporterId,
        string $reason
    ) {
        $this->targetType = $targetType;
        $this->targetId = $targetId;
        $this->reporterId = $reporterId;
        $this->reason = $reason;
        $this->status = 'pending';
        $this->resolvedById = null;
        $this->resolvedAt = null;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTargetType(): string
    {
        return $this->targetType;
    }

    public function getTargetId(): string
    {
        return $this->targetId;
    }

    public function getReporterId(): string
    {
        return $this->reporterId;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getResolvedById(): ?string
    {
        return $this->resolvedById;
    }

    public function getResolvedAt(): ?DateTimeImmutable
    {
        return $this->resolvedAt;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function approve(string $moderatorId): void
    {
        $this->status = 'approved';
        $this->resolvedById = $moderatorId;
        $this->resolvedAt = new DateTimeImmutable();
    }

    public function reject(string $moderatorId): void
    {
        $this->status = 'rejected';
        $this->resolvedById = $moderatorId;
        $this->resolvedAt = new DateTimeImmutable();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function isResolved(): bool
    {
        return $this->resolvedAt !== null;
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
