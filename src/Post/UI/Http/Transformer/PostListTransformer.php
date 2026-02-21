<?php

declare(strict_types=1);

namespace App\Post\UI\Http\Transformer;

use App\Post\Application\DTO\PostDTO;
use App\Post\Application\DTO\PostListDTO;
use League\Fractal\TransformerAbstract;

final class PostListTransformer extends TransformerAbstract
{
    /**
     * @return array<string, mixed>
     */
    public function transform(PostListDTO $postList): array
    {
        return [
            'posts' => array_map(
                fn(PostDTO $dto) => [
                    'id' => $dto->id,
                    'circleId' => $dto->circleId,
                    'authorId' => $dto->authorId,
                    'title' => $dto->title,
                    'content' => $dto->content,
                    'imageUrls' => $dto->imageUrls,
                    'aiSummaryEnabled' => $dto->aiSummaryEnabled,
                    'createdAt' => $dto->createdAt->format(\DateTimeInterface::ATOM),
                    'updatedAt' => $dto->updatedAt->format(\DateTimeInterface::ATOM),
                ],
                $postList->posts
            ),
            'pagination' => [
                'total' => $postList->total,
                'limit' => $postList->limit,
                'offset' => $postList->offset,
            ],
        ];
    }
}
