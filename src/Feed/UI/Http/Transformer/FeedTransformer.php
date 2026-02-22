<?php

declare(strict_types=1);

namespace App\Feed\UI\Http\Transformer;

use App\Feed\Application\DTO\FeedDTO;
use App\Post\UI\Http\Transformer\PostTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

final class FeedTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = ['posts'];

    /**
     * @return array<string, mixed>
     */
    public function transform(FeedDTO $feed): array
    {
        return [
            'pagination' => [
                'total' => $feed->total,
                'limit' => $feed->limit,
                'offset' => $feed->offset,
            ],
        ];
    }

    public function includePosts(FeedDTO $feed): Collection
    {
        return $this->collection($feed->posts, new PostTransformer());
    }
}
