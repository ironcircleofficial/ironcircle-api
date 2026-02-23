<?php

declare(strict_types=1);

namespace App\AI\UI\Http\Voter;

use App\Post\Domain\Model\Post;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class AISummaryVoter extends Voter
{
    public const VIEW = 'AI_SUMMARY_VIEW';
    public const GENERATE = 'AI_SUMMARY_GENERATE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::VIEW, self::GENERATE], true)
            && $subject instanceof Post;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        if ($attribute === self::VIEW) {
            return true;
        }

        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        return match ($attribute) {
            self::GENERATE => $this->canGenerate($user),
            default => false,
        };
    }

    private function canGenerate(UserInterface $user): bool
    {
        return in_array('ROLE_MEMBER', $user->getRoles(), true)
            || in_array('ROLE_MODERATOR', $user->getRoles(), true)
            || in_array('ROLE_ADMIN', $user->getRoles(), true);
    }
}
