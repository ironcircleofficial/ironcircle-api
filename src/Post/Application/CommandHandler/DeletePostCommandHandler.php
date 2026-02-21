<?php

declare(strict_types=1);

namespace App\Post\Application\CommandHandler;

use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Comment\Domain\Repository\CommentRepositoryInterface;
use App\Post\Application\Command\DeletePostCommand;
use App\Post\Domain\Exception\PostNotFoundException;
use App\Post\Domain\Exception\UnauthorizedPostAccessException;
use App\Post\Domain\Repository\PostRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeletePostCommandHandler
{
    public function __construct(
        private PostRepositoryInterface $postRepository,
        private CircleRepositoryInterface $circleRepository,
        private CommentRepositoryInterface $commentRepository
    ) {
    }

    public function __invoke(DeletePostCommand $command): void
    {
        $post = $this->postRepository->findById($command->postId);

        if ($post === null) {
            throw PostNotFoundException::withId($command->postId);
        }

        $circle = $this->circleRepository->findById($post->getCircleId());

        if (!$post->isOwnedBy($command->deletedBy) && ($circle === null || !$circle->isModerator($command->deletedBy))) {
            throw UnauthorizedPostAccessException::notAuthor();
        }

        $this->commentRepository->deleteByPostId($command->postId);
        $this->postRepository->delete($post);
    }
}
