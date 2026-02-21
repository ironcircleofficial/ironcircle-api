<?php

declare(strict_types=1);

namespace App\Comment\UI\Http\Transformer;

use App\Comment\Application\DTO\CommentDTO;
use League\Fractal\TransformerAbstract;

final class CommentTransformer extends TransformerAbstract
{
    /**
     * @return array<string, mixed>
     */
    public function transform(CommentDTO $comment): array
    {
        return [
            'id' => $comment->id,
            'postId' => $comment->postId,
            'authorId' => $comment->authorId,
            'parentCommentId' => $comment->parentCommentId,
            'content' => $comment->content,
            'createdAt' => $comment->createdAt->format(\DateTimeInterface::ATOM),
            'updatedAt' => $comment->updatedAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
