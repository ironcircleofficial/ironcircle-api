<?php

declare(strict_types=1);

namespace App\Admin\Application\CommandHandler;

use App\Admin\Application\Command\AdminForceDeleteCircleCommand;
use App\Circle\Domain\Exception\CircleNotFoundException;
use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Post\Application\Command\DeletePostCommand;
use App\Post\Domain\Repository\PostRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class AdminForceDeleteCircleCommandHandler
{
    public function __construct(
        private CircleRepositoryInterface $circleRepository,
        private PostRepositoryInterface $postRepository,
        private MessageBusInterface $messageBus
    ) {
    }

    public function __invoke(AdminForceDeleteCircleCommand $command): void
    {
        $circle = $this->circleRepository->findById($command->circleId);

        if ($circle === null) {
            throw CircleNotFoundException::withId($command->circleId);
        }

        $this->deleteAllPostsInCircle($command->circleId, $command->deletedBy);
        $this->circleRepository->delete($circle);
    }

    private function deleteAllPostsInCircle(string $circleId, string $deletedBy): void
    {
        $totalPosts = $this->postRepository->countByCircle($circleId);

        if ($totalPosts === 0) {
            return;
        }

        $posts = $this->postRepository->findByCircle($circleId, $totalPosts, 0);

        foreach ($posts as $post) {
            $this->messageBus->dispatch(new DeletePostCommand($post->getId(), $deletedBy));
        }
    }
}
