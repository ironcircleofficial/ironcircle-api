<?php

declare(strict_types=1);

namespace App\Post\Application\CommandHandler;

use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Post\Application\Command\DeletePostAttachmentCommand;
use App\Post\Domain\Exception\PostAttachmentNotFoundException;
use App\Post\Domain\Exception\UnauthorizedPostAccessException;
use App\Post\Domain\Repository\PostAttachmentRepositoryInterface;
use App\Post\Domain\Repository\PostRepositoryInterface;
use App\Shared\Application\Service\FileStorageInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeletePostAttachmentCommandHandler
{
    private const UPLOAD_DIRECTORY = 'post_attachments';

    public function __construct(
        private PostAttachmentRepositoryInterface $attachmentRepository,
        private PostRepositoryInterface $postRepository,
        private CircleRepositoryInterface $circleRepository,
        private FileStorageInterface $fileStorage
    ) {
    }

    public function __invoke(DeletePostAttachmentCommand $command): void
    {
        $attachment = $this->attachmentRepository->findById($command->attachmentId);

        if ($attachment === null) {
            throw PostAttachmentNotFoundException::withId($command->attachmentId);
        }

        $post = $this->postRepository->findById($attachment->getPostId());
        $isOwner = $attachment->isOwnedBy($command->deletedBy);
        $isPostOwner = $post !== null && $post->isOwnedBy($command->deletedBy);

        $isModerator = false;
        if ($post !== null) {
            $circle = $this->circleRepository->findById($post->getCircleId());
            if ($circle !== null) {
                $isModerator = $circle->isModerator($command->deletedBy) || $circle->isCreator($command->deletedBy);
            }
        }

        if (!$isOwner && !$isPostOwner && !$isModerator) {
            throw UnauthorizedPostAccessException::notAuthor();
        }

        $this->fileStorage->delete($attachment->getStoredFilename(), self::UPLOAD_DIRECTORY);
        $this->attachmentRepository->delete($attachment);
    }
}
