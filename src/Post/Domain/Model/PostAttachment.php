<?php

declare(strict_types=1);

namespace App\Post\Domain\Model;

use DateTimeImmutable;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: 'post_attachments')]
#[MongoDB\Index(keys: ['postId' => 'asc'])]
#[MongoDB\Index(keys: ['postId' => 'asc', 'uploadedAt' => 'desc'])]
class PostAttachment
{
    #[MongoDB\Id]
    private string $id;

    #[MongoDB\Field(type: 'string')]
    private readonly string $postId;

    #[MongoDB\Field(type: 'string')]
    private readonly string $authorId;

    #[MongoDB\Field(type: 'string')]
    private readonly string $originalFilename;

    #[MongoDB\Field(type: 'string')]
    private readonly string $storedFilename;

    #[MongoDB\Field(type: 'string')]
    private readonly string $mimeType;

    #[MongoDB\Field(type: 'int')]
    private readonly int $size;

    #[MongoDB\Field(type: 'date_immutable')]
    private readonly DateTimeImmutable $uploadedAt;

    public function __construct(
        string $postId,
        string $authorId,
        string $originalFilename,
        string $storedFilename,
        string $mimeType,
        int $size
    ) {
        $this->postId = $postId;
        $this->authorId = $authorId;
        $this->originalFilename = $originalFilename;
        $this->storedFilename = $storedFilename;
        $this->mimeType = $mimeType;
        $this->size = $size;
        $this->uploadedAt = new DateTimeImmutable();
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

    public function getOriginalFilename(): string
    {
        return $this->originalFilename;
    }

    public function getStoredFilename(): string
    {
        return $this->storedFilename;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getUploadedAt(): DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mimeType, 'image/');
    }

    public function isOwnedBy(string $userId): bool
    {
        return $this->authorId === $userId;
    }
}
