<?php

declare(strict_types=1);

namespace App\Moderation\UI\Http\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class FlagVoter extends Voter
{
    public const FLAG_CREATE = 'FLAG_CREATE';
    public const FLAG_LIST   = 'FLAG_LIST';
    public const FLAG_RESOLVE = 'FLAG_RESOLVE';
    public const FLAG_DISMISS = 'FLAG_DISMISS';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [
            self::FLAG_CREATE,
            self::FLAG_LIST,
            self::FLAG_RESOLVE,
            self::FLAG_DISMISS,
        ], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::FLAG_CREATE  => $this->canCreate($user),
            self::FLAG_LIST,
            self::FLAG_RESOLVE,
            self::FLAG_DISMISS => $this->isModeratorOrAdmin($user),
            default            => false,
        };
    }

    private function canCreate(UserInterface $user): bool
    {
        return in_array('ROLE_MEMBER', $user->getRoles(), true)
            || in_array('ROLE_MODERATOR', $user->getRoles(), true)
            || in_array('ROLE_ADMIN', $user->getRoles(), true);
    }

    private function isModeratorOrAdmin(UserInterface $user): bool
    {
        return in_array('ROLE_MODERATOR', $user->getRoles(), true)
            || in_array('ROLE_ADMIN', $user->getRoles(), true);
    }
}
