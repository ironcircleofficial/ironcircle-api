<?php

declare(strict_types=1);

namespace App\Post\Domain\Model;

use DateTimeImmutable;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: 'posts')]
#[MongoDB\Index(keys: ['circleId' => 'asc'])]
#[MongoDB\Index(keys: ['authorId' => 'asc'])]
#[MongoDB\Index(keys: ['createdAt' => 'desc'])]
#[MongoDB\Index(keys: ['circleId' => 'asc', 'createdAt' => 'desc'])]
class Post
{
    #[MongoDB\Id]
    private string $id;

    #[MongoDB\Field(type: 'string')]
    private readonly string $circleId;

    #[MongoDB\Field(type: 'string')]
    private readonly string $authorId;

    #[MongoDB\Field(type: 'string')]
    private string $title;

    #[MongoDB\Field(type: 'string')]
    private string $content;

    /**
     * @var array<string>
     */
    #[MongoDB\Field(type: 'collection')]
    private array $imageUrls;

    #[MongoDB\Field(type: 'bool')]
    private bool $aiSummaryEnabled;

    #[MongoDB\Field(type: 'date_immutable')]
    private readonly DateTimeImmutable $createdAt;

    #[MongoDB\Field(type: 'date_immutable')]
    private DateTimeImmutable $updatedAt;

    /**
     * @param array<string> $imageUrls
     */
    public function __construct(
        string $circleId,
        string $authorId,
        string $title,
        string $content,
        array $imageUrls = [],
        bool $aiSummaryEnabled = false
    ) {
        $this->circleId = $circleId;
        $this->authorId = $authorId;
        $this->title = $title;
        $this->content = $content;
        $this->imageUrls = $imageUrls;
        $this->aiSummaryEnabled = $aiSummaryEnabled;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCircleId(): string
    {
        return $this->circleId;
    }

    public function getAuthorId(): string
    {
        return $this->authorId;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    /**
     * @return array<string>
     */
    public function getImageUrls(): array
    {
        return $this->imageUrls;
    }

    public function isAiSummaryEnabled(): bool
    {
        return $this->aiSummaryEnabled;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @param array<string> $imageUrls
     */
    public function updateContent(string $title, string $content, array $imageUrls): void
    {
        $this->title = $title;
        $this->content = $content;
        $this->imageUrls = $imageUrls;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function enableAiSummary(): void
    {
        $this->aiSummaryEnabled = true;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function disableAiSummary(): void
    {
        $this->aiSummaryEnabled = false;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isOwnedBy(string $userId): bool
    {
        return $this->authorId === $userId;
    }
}
