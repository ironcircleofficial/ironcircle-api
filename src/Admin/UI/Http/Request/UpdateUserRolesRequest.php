<?php

declare(strict_types=1);

namespace App\Admin\UI\Http\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateUserRolesRequest
{
    /**
     * @param array<string> $roles
     */
    public function __construct(
        #[Assert\NotBlank(message: 'Roles must not be empty')]
        #[Assert\Count(min: 1, minMessage: 'At least one role is required')]
        #[Assert\All([
            new Assert\Choice(
                choices: ['ROLE_ADMIN', 'ROLE_MODERATOR', 'ROLE_MEMBER'],
                message: 'Invalid role "{{ value }}". Valid roles are: ROLE_ADMIN, ROLE_MODERATOR, ROLE_MEMBER'
            )
        ])]
        public array $roles
    ) {
    }
}
