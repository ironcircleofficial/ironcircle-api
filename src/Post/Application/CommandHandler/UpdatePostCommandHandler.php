<?php

declare(strict_types=1);

namespace App\Post\Application\CommandHandler;

use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Post\Application\Command\UpdatePostCommand;
use App\Post\Domain\Exception\PostNotFoundException;
use App\Post\Domain\Exception\UnauthorizedPostAccessException;
use App\Post\Domain\Repository\PostRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UpdatePostCommandHandler
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private CircleRepositoryInterface $circleRepository
    ) {
    }

    public function __invoke(UpdatePostCommand $command): void
    {
        $post = $this->postRepository->findById($command->postId);

        if ($post === null) {
            throw PostNotFoundException::withId($command->postId);
        }

        $circle = $this->circleRepository->findById($post->getCircleId());

        if (!$post->isOwnedBy($command->updatedBy) && ($circle === null || !$circle->isModerator($command->updatedBy))) {
            throw UnauthorizedPostAccessException::notAuthor();
        }

        $post->updateContent($command->title, $command->content);
        $this->postRepository->save($post);
    }
}
