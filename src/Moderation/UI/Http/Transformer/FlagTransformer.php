<?php

declare(strict_types=1);

namespace App\Moderation\UI\Http\Transformer;

use App\Moderation\Application\DTO\FlagDTO;
use App\User\UI\Http\Transformer\UserInlineTransformer;
use League\Fractal\TransformerAbstract;

final class FlagTransformer extends TransformerAbstract
{
    /**
     * @return array<string, mixed>
     */
    public function transform(FlagDTO $flag): array
    {
        $userInlineTransformer = new UserInlineTransformer();

        return [
            'id'         => $flag->id,
            'targetType' => $flag->targetType,
            'targetId'   => $flag->targetId,
            'reporter'   => $userInlineTransformer->transform($flag->reporter),
            'reason'     => $flag->reason,
            'status'     => $flag->status,
            'resolvedBy' => $flag->resolvedBy !== null
                ? $userInlineTransformer->transform($flag->resolvedBy)
                : null,
            'resolvedAt' => $flag->resolvedAt?->format(\DateTimeInterface::ATOM),
            'createdAt'  => $flag->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
