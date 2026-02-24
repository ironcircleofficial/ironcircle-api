<?php

declare(strict_types=1);

namespace App\Admin\Domain\Exception;

use DomainException;

final class CannotDeleteAdminException extends DomainException
{
    public static function create(): self
    {
        return new self('Cannot delete a user with ROLE_ADMIN');
    }
}
