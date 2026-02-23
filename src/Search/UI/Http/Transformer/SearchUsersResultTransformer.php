<?php

declare(strict_types=1);

namespace App\Search\UI\Http\Transformer;

use App\Search\Application\DTO\SearchUsersResultDTO;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

final class SearchUsersResultTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = ['users'];

    /**
     * @return array<string, mixed>
     */
    public function transform(SearchUsersResultDTO $result): array
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

    public function includeUsers(SearchUsersResultDTO $result): Collection
    {
        return $this->collection($result->users, new SearchUserTransformer());
    }
}
