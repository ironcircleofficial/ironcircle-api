<?php

declare(strict_types=1);

namespace App\Vote\UI\Http\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class VoteVoter extends Voter
{
    public const CAST = 'VOTE_CAST';
    public const RETRACT = 'VOTE_RETRACT';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::CAST, self::RETRACT], true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();

        if (!$currentUser instanceof UserInterface) {
            return false;
        }

        return $this->hasRequiredRole($currentUser);
    }

    private function hasRequiredRole(UserInterface $user): bool
    {
        return in_array('ROLE_MEMBER', $user->getRoles(), true)
            || in_array('ROLE_MODERATOR', $user->getRoles(), true)
            || in_array('ROLE_ADMIN', $user->getRoles(), true);
    }
}
