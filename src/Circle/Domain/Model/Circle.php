<?php

declare(strict_types=1);

namespace App\Circle\Domain\Model;

use DateTimeImmutable;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: 'circles')]
#[MongoDB\Index(keys: ['slug' => 'asc'], options: ['unique' => true])]
#[MongoDB\Index(keys: ['name' => 'asc'])]
#[MongoDB\Index(keys: ['creatorId' => 'asc'])]
#[MongoDB\Index(keys: ['visibility' => 'asc'])]
class Circle
{
    #[MongoDB\Id]
    private string $id;

    #[MongoDB\Field(type: 'string')]
    private string $name;

    #[MongoDB\Field(type: 'string')]
    private readonly string $slug;

    #[MongoDB\Field(type: 'string')]
    private string $description;

    #[MongoDB\Field(type: 'string')]
    private string $visibility;

    #[MongoDB\Field(type: 'string')]
    private readonly string $creatorId;

    /**
     * @var array<string>
     */
    #[MongoDB\Field(type: 'collection')]
    private array $moderatorIds;

    #[MongoDB\Field(type: 'date_immutable')]
    private readonly DateTimeImmutable $createdAt;

    #[MongoDB\Field(type: 'date_immutable')]
    private DateTimeImmutable $updatedAt;

    /**
     * @param array<string> $moderatorIds
     */
    public function __construct(
        string $name,
        string $slug,
        string $description,
        string $visibility,
        string $creatorId,
        array $moderatorIds = []
    ) {
        $this->name = $name;
        $this->slug = $slug;
        $this->description = $description;
        $this->visibility = $visibility;
        $this->creatorId = $creatorId;
        $this->moderatorIds = $moderatorIds;
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getCreatorId(): string
    {
        return $this->creatorId;
    }

    /**
     * @return array<string>
     */
    public function getModeratorIds(): array
    {
        return $this->moderatorIds;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function updateDetails(string $name, string $description): void
    {
        $this->name = $name;
        $this->description = $description;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function changeVisibility(string $visibility): void
    {
        $this->visibility = $visibility;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function addModerator(string $userId): void
    {
        if (!in_array($userId, $this->moderatorIds, true)) {
            $this->moderatorIds[] = $userId;
            $this->updatedAt = new DateTimeImmutable();
        }
    }

    public function removeModerator(string $userId): void
    {
        $this->moderatorIds = array_values(
            array_filter($this->moderatorIds, fn($id) => $id !== $userId)
        );
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isModerator(string $userId): bool
    {
        return in_array($userId, $this->moderatorIds, true);
    }

    public function isCreator(string $userId): bool
    {
        return $this->creatorId === $userId;
    }

    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }
}
