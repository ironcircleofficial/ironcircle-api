<?php

declare(strict_types=1);

namespace App\Post\UI\Http\Transformer;

use App\Post\Application\DTO\PostDTO;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

final class PostTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = ['attachments'];

    /**
     * @return array<string, mixed>
     */
    public function transform(PostDTO $post): array
    {
        return [
            'id' => $post->id,
            'circleId' => $post->circleId,
            'authorId' => $post->authorId,
            'title' => $post->title,
            'content' => $post->content,
            'aiSummaryEnabled' => $post->aiSummaryEnabled,
            'createdAt' => $post->createdAt->format(\DateTimeInterface::ATOM),
            'updatedAt' => $post->updatedAt->format(\DateTimeInterface::ATOM),
        ];
    }

    public function includeAttachments(PostDTO $post): Collection
    {
        return $this->collection($post->attachments, new PostAttachmentTransformer());
    }
}
