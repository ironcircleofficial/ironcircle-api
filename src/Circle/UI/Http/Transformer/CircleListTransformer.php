<?php

declare(strict_types=1);

namespace App\Circle\UI\Http\Transformer;

use App\Circle\Application\DTO\CircleListDTO;
use League\Fractal\TransformerAbstract;

final class CircleListTransformer extends TransformerAbstract
{
    public function __construct(
        private readonly CircleTransformer $circleTransformer
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function transform(CircleListDTO $circleListDTO): array
    {
        $circles = array_map(
            fn($circleDTO) => $this->circleTransformer->transform($circleDTO),
            $circleListDTO->circles
        );

        return [
            'circles' => $circles,
            'pagination' => [
                'total' => $circleListDTO->total,
                'limit' => $circleListDTO->limit,
                'offset' => $circleListDTO->offset,
            ],
        ];
    }
}
