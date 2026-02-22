<?php

declare(strict_types=1);

namespace App\Moderation\UI\Http\Transformer;

use App\Moderation\Application\DTO\FlagListDTO;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

final class FlagListTransformer extends TransformerAbstract
{
    protected array $defaultIncludes = ['flags'];

    /**
     * @return array<string, mixed>
     */
    public function transform(FlagListDTO $flagList): array
    {
        return [
            'pagination' => [
                'total'  => $flagList->total,
                'limit'  => $flagList->limit,
                'offset' => $flagList->offset,
            ],
        ];
    }

    public function includeFlags(FlagListDTO $flagList): Collection
    {
        return $this->collection($flagList->flags, new FlagTransformer());
    }
}
