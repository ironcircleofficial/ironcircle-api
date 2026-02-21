<?php

declare(strict_types=1);

namespace App\Post\UI\Http\Voter;

use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Post\Domain\Model\PostAttachment;
use App\Post\Domain\Repository\PostRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class PostAttachmentVoter extends Voter
{
    public const UPLOAD = 'POST_ATTACHMENT_UPLOAD';
    public const DELETE = 'POST_ATTACHMENT_DELETE';

    public function __construct(
        private readonly PostRepositoryInterface $postRepository,
        private readonly CircleRepositoryInterface $circleRepository
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if ($attribute === self::UPLOAD) {
            return true;
        }

        return $attribute === self::DELETE && $subject instanceof PostAttachment;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $currentUser = $token->getUser();

        if (!$currentUser instanceof UserInterface) {
            return false;
        }

        $userId = method_exists($currentUser, 'getId') ? $currentUser->getId() : null;

        if ($userId === null) {
            return false;
        }

        return match ($attribute) {
            self::UPLOAD => $this->canUpload($currentUser),
            self::DELETE => $this->canDelete($subject, $userId, $currentUser),
            default => false,
        };
    }

    private function canUpload(UserInterface $currentUser): bool
    {
        return in_array('ROLE_MEMBER', $currentUser->getRoles(), true)
            || in_array('ROLE_MODERATOR', $currentUser->getRoles(), true)
            || in_array('ROLE_ADMIN', $currentUser->getRoles(), true);
    }

    private function canDelete(PostAttachment $attachment, string $userId, UserInterface $currentUser): bool
    {
        if ($attachment->isOwnedBy($userId)) {
            return true;
        }

        if (in_array('ROLE_ADMIN', $currentUser->getRoles(), true)) {
            return true;
        }

        $post = $this->postRepository->findById($attachment->getPostId());

        if ($post === null) {
            return false;
        }

        if ($post->isOwnedBy($userId)) {
            return true;
        }

        $circle = $this->circleRepository->findById($post->getCircleId());

        if ($circle === null) {
            return false;
        }

        return $circle->isModerator($userId) || $circle->isCreator($userId);
    }
}
