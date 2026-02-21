<?php

declare(strict_types=1);

namespace App\Post\Application\CommandHandler;

use App\Post\Application\Command\ToggleAiSummaryCommand;
use App\Post\Domain\Exception\PostNotFoundException;
use App\Post\Domain\Exception\UnauthorizedPostAccessException;
use App\Post\Domain\Repository\PostRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class ToggleAiSummaryCommandHandler
{
    public function __construct(
        private PostRepositoryInterface $postRepository
    ) {
    }

    public function __invoke(ToggleAiSummaryCommand $command): void
    {
        $post = $this->postRepository->findById($command->postId);

        if ($post === null) {
            throw PostNotFoundException::withId($command->postId);
        }

        if (!$post->isOwnedBy($command->updatedBy)) {
            throw UnauthorizedPostAccessException::notAuthor();
        }

        if ($command->enabled) {
            $post->enableAiSummary();
        } else {
            $post->disableAiSummary();
        }

        $this->postRepository->save($post);
    }
}
