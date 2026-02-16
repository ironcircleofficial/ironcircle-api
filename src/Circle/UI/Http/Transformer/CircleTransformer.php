<?php

declare(strict_types=1);

namespace App\Circle\UI\Http\Transformer;

use App\Circle\Application\DTO\CircleDTO;
use League\Fractal\TransformerAbstract;

final class CircleTransformer extends TransformerAbstract
{
    /**
     * @return array<string, mixed>
     */
    public function transform(CircleDTO $circleDTO): array
    {
        return [
            'id' => $circleDTO->id,
            'name' => $circleDTO->name,
            'slug' => $circleDTO->slug,
            'description' => $circleDTO->description,
            'visibility' => $circleDTO->visibility,
            'creatorId' => $circleDTO->creatorId,
            'moderatorIds' => $circleDTO->moderatorIds,
            'createdAt' => $circleDTO->createdAt->format(DATE_ATOM),
            'updatedAt' => $circleDTO->updatedAt->format(DATE_ATOM),
        ];
    }
}
