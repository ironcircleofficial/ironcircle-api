<?php

declare(strict_types=1);

namespace App\User\UI\Http\Transformer;

use App\User\Application\DTO\UserInlineDTO;
use League\Fractal\TransformerAbstract;

final class UserInlineTransformer extends TransformerAbstract
{
    /**
     * @return array<string, mixed>
     */
    public function transform(UserInlineDTO $user): array
    {
        return [
            'id'   => $user->id,
            'name' => $user->username,
        ];
    }
}
