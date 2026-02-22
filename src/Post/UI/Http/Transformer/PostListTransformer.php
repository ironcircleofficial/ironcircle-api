<?php

declare(strict_types=1);

namespace App\Post\UI\Http\Transformer;

use App\Post\Application\DTO\PostListDTO;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

final class PostListTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = ['posts'];

    /**
     * @return array<string, mixed>
     */
    public function transform(PostListDTO $postList): array
    {
        return [
            'pagination' => [
                'total' => $postList->total,
                'limit' => $postList->limit,
                'offset' => $postList->offset,
            ],
        ];
    }

    public function includePosts(PostListDTO $postList): Collection
    {
        return $this->collection($postList->posts, new PostTransformer());
    }
}
