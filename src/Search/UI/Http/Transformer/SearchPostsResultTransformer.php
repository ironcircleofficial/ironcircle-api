<?php

declare(strict_types=1);

namespace App\Search\UI\Http\Transformer;

use App\Post\UI\Http\Transformer\PostTransformer;
use App\Search\Application\DTO\SearchPostsResultDTO;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

final class SearchPostsResultTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = ['posts'];

    /**
     * @return array<string, mixed>
     */
    public function transform(SearchPostsResultDTO $result): array
    {
        return [
            'query' => $result->query,
            'pagination' => [
                'total' => $result->total,
                'limit' => $result->limit,
                'offset' => $result->offset,
            ],
        ];
    }

    public function includePosts(SearchPostsResultDTO $result): Collection
    {
        return $this->collection($result->posts, new PostTransformer());
    }
}
