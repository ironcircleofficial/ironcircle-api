<?php

declare(strict_types=1);

namespace App\Circle\UI\Http\Transformer;

use App\Circle\Application\DTO\CircleListDTO;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

final class CircleListTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = ['circles'];

    /**
     * @return array<string, mixed>
     */
    public function transform(CircleListDTO $circleListDTO): array
    {
        return [
            'pagination' => [
                'total' => $circleListDTO->total,
                'limit' => $circleListDTO->limit,
                'offset' => $circleListDTO->offset,
            ],
        ];
    }

    public function includeCircles(CircleListDTO $circleListDTO): Collection
    {
        return $this->collection($circleListDTO->circles, new CircleTransformer());
    }
}
