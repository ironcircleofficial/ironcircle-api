<?php

declare(strict_types=1);

namespace App\Admin\UI\Http\Transformer;

use App\Admin\Application\DTO\AdminUserListDTO;
use League\Fractal\Resource\Collection;
use League\Fractal\TransformerAbstract;

final class AdminUserListTransformer extends TransformerAbstract
{
    /** @var array<string> */
    protected array $defaultIncludes = ['users'];

    /**
     * @return array<string, mixed>
     */
    public function transform(AdminUserListDTO $userListDTO): array
    {
        return [
            'pagination' => [
                'total' => $userListDTO->total,
                'limit' => $userListDTO->limit,
                'offset' => $userListDTO->offset,
            ],
        ];
    }

    public function includeUsers(AdminUserListDTO $userListDTO): Collection
    {
        return $this->collection($userListDTO->users, new AdminUserTransformer());
    }
}
