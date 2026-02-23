<?php

declare(strict_types=1);

namespace App\Search\UI\Http\Transformer;

use App\Search\Application\DTO\SearchUserDTO;
use League\Fractal\TransformerAbstract;

final class SearchUserTransformer extends TransformerAbstract
{
    /**
     * @return array<string, mixed>
     */
    public function transform(SearchUserDTO $user): array
    {
        return [
            'id' => $user->id,
            'username' => $user->username,
            'roles' => $user->roles,
            'createdAt' => $user->createdAt->format(DATE_ATOM),
        ];
    }
}
