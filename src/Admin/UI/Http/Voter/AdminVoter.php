<?php

declare(strict_types=1);

namespace App\Admin\UI\Http\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class AdminVoter extends Voter
{
    public const ADMIN_LIST_USERS = 'ADMIN_LIST_USERS';
    public const ADMIN_VIEW_USER = 'ADMIN_VIEW_USER';
    public const ADMIN_UPDATE_USER_ROLES = 'ADMIN_UPDATE_USER_ROLES';
    public const ADMIN_DELETE_USER = 'ADMIN_DELETE_USER';
    public const ADMIN_LIST_CIRCLES = 'ADMIN_LIST_CIRCLES';
    public const ADMIN_FORCE_DELETE_CIRCLE = 'ADMIN_FORCE_DELETE_CIRCLE';

    private const SUPPORTED_ATTRIBUTES = [
        self::ADMIN_LIST_USERS,
        self::ADMIN_VIEW_USER,
        self::ADMIN_UPDATE_USER_ROLES,
        self::ADMIN_DELETE_USER,
        self::ADMIN_LIST_CIRCLES,
        self::ADMIN_FORCE_DELETE_CIRCLE,
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, self::SUPPORTED_ATTRIBUTES, true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();

        if (!$currentUser instanceof UserInterface) {
            return false;
        }

        return in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
    }
}
