<?php

declare(strict_types=1);

namespace App\Post\UI\Http\Transformer;

use App\Post\Application\DTO\PostAttachmentDTO;
use League\Fractal\TransformerAbstract;

final class PostAttachmentTransformer extends TransformerAbstract
{
    /**
     * @return array<string, mixed>
     */
    public function transform(PostAttachmentDTO $attachment): array
    {
        return [
            'id' => $attachment->id,
            'postId' => $attachment->postId,
            'authorId' => $attachment->authorId,
            'originalFilename' => $attachment->originalFilename,
            'mimeType' => $attachment->mimeType,
            'size' => $attachment->size,
            'isImage' => str_starts_with($attachment->mimeType, 'image/'),
            'uploadedAt' => $attachment->uploadedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
