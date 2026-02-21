<?php

declare(strict_types=1);

namespace App\Comment\UI\Http\Transformer;

use App\Comment\Application\DTO\CommentDTO;
use App\Comment\Application\DTO\CommentListDTO;
use League\Fractal\TransformerAbstract;

final class CommentListTransformer extends TransformerAbstract
{
    /**
     * @return array<string, mixed>
     */
    public function transform(CommentListDTO $commentList): array
    {
        return [
            'comments' => array_map(
                fn(CommentDTO $dto) => [
                    'id' => $dto->id,
                    'postId' => $dto->postId,
                    'authorId' => $dto->authorId,
                    'parentCommentId' => $dto->parentCommentId,
                    'content' => $dto->content,
                    'createdAt' => $dto->createdAt->format(\DateTimeInterface::ATOM),
                    'updatedAt' => $dto->updatedAt->format(\DateTimeInterface::ATOM),
                ],
                $commentList->comments
            ),
            'pagination' => [
                'total' => $commentList->total,
                'limit' => $commentList->limit,
                'offset' => $commentList->offset,
            ],
        ];
    }
}
