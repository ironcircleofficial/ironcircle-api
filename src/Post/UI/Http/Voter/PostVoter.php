<?php

declare(strict_types=1);

namespace App\Post\UI\Http\Voter;

use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Post\Domain\Model\Post;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class PostVoter extends Voter
{
    public const CREATE = 'POST_CREATE';
    public const VIEW = 'POST_VIEW';
    public const UPDATE = 'POST_UPDATE';
    public const DELETE = 'POST_DELETE';
    public const TOGGLE_AI_SUMMARY = 'POST_TOGGLE_AI_SUMMARY';

    public function __construct(
        private readonly CircleRepositoryInterface $circleRepository
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute === self::CREATE) {
            return true;
        }

        return in_array($attribute, [self::VIEW, self::UPDATE, self::DELETE, self::TOGGLE_AI_SUMMARY], true)
            && $subject instanceof Post;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($attribute === self::VIEW) {
            return true;
        }

        $currentUser = $token->getUser();

        if (!$currentUser instanceof UserInterface) {
            return false;
        }

        if ($attribute === self::CREATE) {
            return $this->canCreate($currentUser);
        }

        /** @var Post $post */
        $post = $subject;

        $userId = method_exists($currentUser, 'getId') ? $currentUser->getId() : null;

        if ($userId === null) {
            return false;
        }

        return match ($attribute) {
            self::UPDATE => $this->canUpdate($post, $userId, $currentUser),
            self::DELETE => $this->canDelete($post, $userId, $currentUser),
            self::TOGGLE_AI_SUMMARY => $this->canToggleAiSummary($post, $userId),
            default => false,
        };
    }

    private function canCreate(UserInterface $currentUser): bool
    {
        return in_array('ROLE_MEMBER', $currentUser->getRoles(), true)
            || in_array('ROLE_MODERATOR', $currentUser->getRoles(), true)
            || in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
    }

    private function canUpdate(Post $post, string $userId, UserInterface $currentUser): bool
    {
        if ($post->isOwnedBy($userId)) {
            return true;
        }

        if (in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            return true;
        }

        $circle = $this->circleRepository->findById($post->getCircleId());

        if ($circle === null) {
            return false;
        }

        return $circle->isModerator($userId) || $circle->isCreator($userId);
    }

    private function canDelete(Post $post, string $userId, UserInterface $currentUser): bool
    {
        if ($post->isOwnedBy($userId)) {
            return true;
        }

        if (in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            return true;
        }

        $circle = $this->circleRepository->findById($post->getCircleId());

        if ($circle === null) {
            return false;
        }

        return $circle->isModerator($userId) || $circle->isCreator($userId);
    }

    private function canToggleAiSummary(Post $post, string $userId): bool
    {
        return $post->isOwnedBy($userId);
    }
}
