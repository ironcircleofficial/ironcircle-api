<?php

declare(strict_types=1);

namespace App\Moderation\UI\Http\Transformer;

use App\Moderation\Application\DTO\FlagDTO;
use League\Fractal\TransformerAbstract;

final class FlagTransformer extends TransformerAbstract
{
    /**
     * @return array<string, mixed>
     */
    public function transform(FlagDTO $flag): array
    {
        return [
            'id'           => $flag->id,
            'targetType'   => $flag->targetType,
            'targetId'     => $flag->targetId,
            'reporterId'   => $flag->reporterId,
            'reason'       => $flag->reason,
            'status'       => $flag->status,
            'resolvedById' => $flag->resolvedById,
            'resolvedAt'   => $flag->resolvedAt?->format(\DateTimeInterface::ATOM),
            'createdAt'    => $flag->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
