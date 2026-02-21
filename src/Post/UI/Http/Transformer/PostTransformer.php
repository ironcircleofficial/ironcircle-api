<?php

declare(strict_types=1);

namespace App\Post\UI\Http\Transformer;

use App\Post\Application\DTO\PostAttachmentDTO;
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
            'aiSummaryEnabled' => $post->aiSummaryEnabled,
            'attachments' => array_map(
                fn(PostAttachmentDTO $attachment) => [
                    'id' => $attachment->id,
                    'originalFilename' => $attachment->originalFilename,
                    'mimeType' => $attachment->mimeType,
                    'size' => $attachment->size,
                    'uploadedAt' => $attachment->uploadedAt->format(\DateTimeInterface::ATOM),
                ],
                $post->attachments
            ),
            'createdAt' => $post->createdAt->format(\DateTimeInterface::ATOM),
            'updatedAt' => $post->updatedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
