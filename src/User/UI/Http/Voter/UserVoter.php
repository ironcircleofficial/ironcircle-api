<?php

declare(strict_types=1);

namespace App\User\UI\Http\Voter;

use App\User\Domain\Model\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserVoter extends Voter
{
    public const VIEW = 'USER_VIEW';
    public const EDIT = 'USER_EDIT';
    public const DELETE = 'USER_DELETE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::EDIT, self::DELETE], true)
            && $subject instanceof User;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();

        if (!$currentUser instanceof UserInterface) {
            return false;
        }

        /** @var User $user */
        $user = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($user, $currentUser),
            self::EDIT => $this->canEdit($user, $currentUser),
            self::DELETE => $this->canDelete($user, $currentUser),
            default => false,
        };
    }

    private function canView(User $user, UserInterface $currentUser): bool
    {
        return true;
    }

    private function canEdit(User $user, UserInterface $currentUser): bool
    {
        return $user->getUsername() === $currentUser->getUserIdentifier()
            || in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
    }

    private function canDelete(User $user, UserInterface $currentUser): bool
    {
        return in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
    }
}
