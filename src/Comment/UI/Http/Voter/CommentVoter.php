<?php

declare(strict_types=1);

namespace App\Comment\UI\Http\Voter;

use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Comment\Domain\Model\Comment;
use App\Post\Domain\Repository\PostRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class CommentVoter extends Voter
{
    public const CREATE = 'COMMENT_CREATE';
    public const VIEW = 'COMMENT_VIEW';
    public const UPDATE = 'COMMENT_UPDATE';
    public const DELETE = 'COMMENT_DELETE';

    public function __construct(
        private readonly PostRepositoryInterface $postRepository,
        private readonly CircleRepositoryInterface $circleRepository
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute === self::CREATE) {
            return true;
        }

        return in_array($attribute, [self::VIEW, self::UPDATE, self::DELETE], true)
            && $subject instanceof Comment;
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

        /** @var Comment $comment */
        $comment = $subject;

        $userId = method_exists($currentUser, 'getId') ? $currentUser->getId() : null;

        if ($userId === null) {
            return false;
        }

        return match ($attribute) {
            self::UPDATE => $this->canUpdate($comment, $userId, $currentUser),
            self::DELETE => $this->canDelete($comment, $userId, $currentUser),
            default => false,
        };
    }

    private function canCreate(UserInterface $currentUser): bool
    {
        return in_array('ROLE_MEMBER', $currentUser->getRoles(), true)
            || in_array('ROLE_MODERATOR', $currentUser->getRoles(), true)
            || in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
    }

    private function canUpdate(Comment $comment, string $userId, UserInterface $currentUser): bool
    {
        if ($comment->isOwnedBy($userId)) {
            return true;
        }

        if (in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            return true;
        }

        return $this->isCircleModeratorOrCreator($comment, $userId);
    }

    private function canDelete(Comment $comment, string $userId, UserInterface $currentUser): bool
    {
        if ($comment->isOwnedBy($userId)) {
            return true;
        }

        if (in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            return true;
        }

        return $this->isCircleModeratorOrCreator($comment, $userId);
    }

    private function isCircleModeratorOrCreator(Comment $comment, string $userId): bool
    {
        $post = $this->postRepository->findById($comment->getPostId());

        if ($post === null) {
            return false;
        }

        $circle = $this->circleRepository->findById($post->getCircleId());

        if ($circle === null) {
            return false;
        }

        return $circle->isModerator($userId) || $circle->isCreator($userId);
    }
}
