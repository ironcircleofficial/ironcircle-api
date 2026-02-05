<?php

declare(strict_types=1);

namespace App\AI\Domain\Model;

use DateTimeImmutable;
use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

#[MongoDB\Document(collection: 'ai_summaries')]
#[MongoDB\UniqueIndex(keys: ['postId' => 'asc'])]
class AISummary
{
    #[MongoDB\Id]
    private string $id;

    #[MongoDB\Field(type: 'string')]
    private readonly string $postId;

    #[MongoDB\Field(type: 'string')]
    private readonly string $summary;

    #[MongoDB\Field(type: 'string')]
    private readonly string $model;

    #[MongoDB\Field(type: 'date_immutable')]
    private readonly DateTimeImmutable $generatedAt;

    public function __construct(
        string $postId,
        string $summary,
        string $model
    ) {
        $this->postId = $postId;
        $this->summary = $summary;
        $this->model = $model;
        $this->generatedAt = new DateTimeImmutable();
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getPostId(): string
    {
        return $this->postId;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function getModel(): string
    {
        return $this->model;
    }

    public function getGeneratedAt(): DateTimeImmutable
    {
        return $this->generatedAt;
    }
}
