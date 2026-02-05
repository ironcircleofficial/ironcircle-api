<?php

declare(strict_types=1);

namespace App\Comment\Domain\Model;

use DateTimeImmutable;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: 'comments')]
#[MongoDB\Index(keys: ['postId' => 'asc'])]
#[MongoDB\Index(keys: ['parentCommentId' => 'asc'])]
#[MongoDB\Index(keys: ['authorId' => 'asc'])]
#[MongoDB\Index(keys: ['postId' => 'asc', 'parentCommentId' => 'asc', 'createdAt' => 'asc'])]
class Comment
{
    #[MongoDB\Id]
    private string $id;

    #[MongoDB\Field(type: 'string')]
    private readonly string $postId;

    #[MongoDB\Field(type: 'string')]
    private readonly string $authorId;

    #[MongoDB\Field(type: 'string', nullable: true)]
    private readonly ?string $parentCommentId;

    #[MongoDB\Field(type: 'string')]
    private string $content;

    #[MongoDB\Field(type: 'date_immutable')]
    private readonly DateTimeImmutable $createdAt;

    #[MongoDB\Field(type: 'date_immutable')]
    private DateTimeImmutable $updatedAt;

    public function __construct(
        string $postId,
        string $authorId,
        string $content,
        ?string $parentCommentId = null
    ) {
        $this->postId = $postId;
        $this->authorId = $authorId;
        $this->content = $content;
        $this->parentCommentId = $parentCommentId;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPostId(): string
    {
        return $this->postId;
    }

    public function getAuthorId(): string
    {
        return $this->authorId;
    }

    public function getParentCommentId(): ?string
    {
        return $this->parentCommentId;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateContent(string $content): void
    {
        $this->content = $content;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isOwnedBy(string $userId): bool
    {
        return $this->authorId === $userId;
    }

    public function isReply(): bool
    {
        return $this->parentCommentId !== null;
    }

    public function isTopLevel(): bool
    {
        return $this->parentCommentId === null;
    }
}
