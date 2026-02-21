<?php

declare(strict_types=1);

namespace App\Post\Application\CommandHandler;

use App\Post\Application\Command\CreatePostCommand;
use App\Post\Domain\Model\Post;
use App\Post\Domain\Repository\PostRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CreatePostCommandHandler
{
    public function __construct(
        private PostRepositoryInterface $postRepository
    ) {
    }

    public function __invoke(CreatePostCommand $command): void
    {
        $post = new Post(
            circleId: $command->circleId,
            authorId: $command->authorId,
            title: $command->title,
            content: $command->content,
            imageUrls: $command->imageUrls,
            aiSummaryEnabled: $command->aiSummaryEnabled
        );

        $this->postRepository->save($post);
    }
}
