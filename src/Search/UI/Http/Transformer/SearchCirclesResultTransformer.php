<?php

declare(strict_types=1);

namespace App\Search\UI\Http\Transformer;

use App\Circle\UI\Http\Transformer\CircleTransformer;
use App\Search\Application\DTO\SearchCirclesResultDTO;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

final class SearchCirclesResultTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = ['circles'];

    /**
     * @return array<string, mixed>
     */
    public function transform(SearchCirclesResultDTO $result): array
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

    public function includeCircles(SearchCirclesResultDTO $result): Collection
    {
        return $this->collection($result->circles, new CircleTransformer());
    }
}
