<?php

declare(strict_types=1);

namespace App\Post\UI\Http\Transformer;

use App\Post\Application\DTO\PostDTO;
use League\Fractal\TransformerAbstract;

final class PostTransformer extends TransformerAbstract
{
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
            'imageUrls' => $post->imageUrls,
            'aiSummaryEnabled' => $post->aiSummaryEnabled,
            'createdAt' => $post->createdAt->format(\DateTimeInterface::ATOM),
            'updatedAt' => $post->updatedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
