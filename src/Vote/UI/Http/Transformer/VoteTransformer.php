<?php

declare(strict_types=1);

namespace App\Vote\UI\Http\Transformer;

use App\Vote\Application\DTO\VoteDTO;
use League\Fractal\TransformerAbstract;

final class VoteTransformer extends TransformerAbstract
{
    /**
     * @return array<string, mixed>
     */
    public function transform(VoteDTO $vote): array
    {
        return [
            'id' => $vote->id,
            'userId' => $vote->userId,
            'targetType' => $vote->targetType,
            'targetId' => $vote->targetId,
            'value' => $vote->value,
            'createdAt' => $vote->createdAt->format(\DateTimeInterface::ATOM),
        ];
    }
}
