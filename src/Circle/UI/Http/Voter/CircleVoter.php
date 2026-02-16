<?php

declare(strict_types=1);

namespace App\Circle\UI\Http\Voter;

use App\Circle\Domain\Model\Circle;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class CircleVoter extends Voter
{
    public const VIEW = 'CIRCLE_VIEW';
    public const CREATE = 'CIRCLE_CREATE';
    public const UPDATE = 'CIRCLE_UPDATE';
    public const DELETE = 'CIRCLE_DELETE';
    public const CHANGE_VISIBILITY = 'CIRCLE_CHANGE_VISIBILITY';

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute === self::CREATE) {
            return true;
        }

        return in_array($attribute, [self::VIEW, self::UPDATE, self::DELETE, self::CHANGE_VISIBILITY], true)
            && $subject instanceof Circle;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();

        if ($attribute === self::VIEW && $subject instanceof Circle) {
            if ($subject->isPublic()) {
                return true;
            }

            if (!$currentUser instanceof UserInterface) {
                return false;
            }

            return $this->canView($subject, $currentUser);
        }

        if (!$currentUser instanceof UserInterface) {
            return false;
        }

        if ($attribute === self::CREATE) {
            return $this->canCreate($currentUser);
        }

        /** @var Circle $circle */
        $circle = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($circle, $currentUser),
            self::UPDATE => $this->canUpdate($circle, $currentUser),
            self::DELETE => $this->canDelete($circle, $currentUser),
            self::CHANGE_VISIBILITY => $this->canChangeVisibility($circle, $currentUser),
            default => false,
        };
    }

    private function canCreate(UserInterface $currentUser): bool
    {
        return in_array('ROLE_MEMBER', $currentUser->getRoles(), true)
            || in_array('ROLE_MODERATOR', $currentUser->getRoles(), true)
            || in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
    }

    private function canView(Circle $circle, UserInterface $currentUser): bool
    {
        if ($circle->isPublic()) {
            return true;
        }

        $userId = method_exists($currentUser, 'getId') ? $currentUser->getId() : null;

        if ($userId !== null && ($circle->isCreator($userId) || $circle->isModerator($userId))) {
            return true;
        }

        return in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
    }

    private function canUpdate(Circle $circle, UserInterface $currentUser): bool
    {
        $userId = method_exists($currentUser, 'getId') ? $currentUser->getId() : null;

        if ($userId === null) {
            return false;
        }

        return $circle->isModerator($userId)
            || $circle->isCreator($userId)
            || in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
    }

    private function canDelete(Circle $circle, UserInterface $currentUser): bool
    {
        $userId = method_exists($currentUser, 'getId') ? $currentUser->getId() : null;

        if ($userId === null) {
            return false;
        }

        return $circle->isCreator($userId)
            || in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
    }

    private function canChangeVisibility(Circle $circle, UserInterface $currentUser): bool
    {
        $userId = method_exists($currentUser, 'getId') ? $currentUser->getId() : null;

        if ($userId === null) {
            return false;
        }

        return $circle->isCreator($userId)
            || in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
    }
}
