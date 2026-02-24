<?php

declare(strict_types=1);

namespace App\Admin\UI\Http\Transformer;

use App\Admin\Application\DTO\AdminCircleListDTO;
use App\Circle\UI\Http\Transformer\CircleTransformer;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

final class AdminCircleListTransformer extends TransformerAbstract
{
    /** @var array<string> */
    protected array $defaultIncludes = ['circles'];

    /**
     * @return array<string, mixed>
     */
    public function transform(AdminCircleListDTO $circleListDTO): array
    {
        return [
            'pagination' => [
                'total' => $circleListDTO->total,
                'limit' => $circleListDTO->limit,
                'offset' => $circleListDTO->offset,
            ],
        ];
    }

    public function includeCircles(AdminCircleListDTO $circleListDTO): Collection
    {
        return $this->collection($circleListDTO->circles, new CircleTransformer());
    }
}
