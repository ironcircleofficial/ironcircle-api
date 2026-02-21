<?php

declare(strict_types=1);

namespace App\Post\Application\CommandHandler;

use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Comment\Domain\Repository\CommentRepositoryInterface;
use App\Post\Application\Command\DeletePostCommand;
use App\Post\Domain\Exception\PostNotFoundException;
use App\Post\Domain\Exception\UnauthorizedPostAccessException;
use App\Post\Domain\Repository\PostAttachmentRepositoryInterface;
use App\Post\Domain\Repository\PostRepositoryInterface;
use App\Shared\Application\Service\FileStorageInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeletePostCommandHandler
{
    private const UPLOAD_DIRECTORY = 'post_attachments';

    public function __construct(
        private PostRepositoryInterface $postRepository,
        private CircleRepositoryInterface $circleRepository,
        private CommentRepositoryInterface $commentRepository,
        private PostAttachmentRepositoryInterface $attachmentRepository,
        private FileStorageInterface $fileStorage
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

        $attachments = $this->attachmentRepository->findByPostId($command->postId);
        foreach ($attachments as $attachment) {
            $this->fileStorage->delete($attachment->getStoredFilename(), self::UPLOAD_DIRECTORY);
        }
        $this->attachmentRepository->deleteByPostId($command->postId);

        $this->commentRepository->deleteByPostId($command->postId);
        $this->postRepository->delete($post);
    }
}
