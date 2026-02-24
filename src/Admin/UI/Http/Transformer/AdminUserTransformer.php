<?php

declare(strict_types=1);

namespace App\Admin\UI\Http\Transformer;

use App\Admin\Application\DTO\AdminUserDTO;
use League\Fractal\TransformerAbstract;

final class AdminUserTransformer extends TransformerAbstract
{
    /**
     * @return array<string, mixed>
     */
    public function transform(AdminUserDTO $userDTO): array
    {
        return [
            'id' => $userDTO->id,
            'username' => $userDTO->username,
            'email' => $userDTO->email,
            'roles' => $userDTO->roles,
            'createdAt' => $userDTO->createdAt->format(DATE_ATOM),
            'updatedAt' => $userDTO->updatedAt->format(DATE_ATOM),
        ];
    }
}
