<?php

declare(strict_types=1);

namespace App\Post\Application\CommandHandler;

use App\Post\Application\Command\UploadPostAttachmentCommand;
use App\Post\Domain\Exception\PostNotFoundException;
use App\Post\Domain\Model\PostAttachment;
use App\Post\Domain\Repository\PostAttachmentRepositoryInterface;
use App\Post\Domain\Repository\PostRepositoryInterface;
use App\Shared\Application\Service\FileStorageInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class UploadPostAttachmentCommandHandler
{
    private const UPLOAD_DIRECTORY = 'post_attachments';

    public function __construct(
        private PostRepositoryInterface $postRepository,
        private PostAttachmentRepositoryInterface $attachmentRepository,
        private FileStorageInterface $fileStorage
    ) {
    }

    public function __invoke(UploadPostAttachmentCommand $command): void
    {
        $post = $this->postRepository->findById($command->postId);

        if ($post === null) {
            throw PostNotFoundException::withId($command->postId);
        }

        $storedFilename = $this->generateStoredFilename($command->originalFilename);

        $this->fileStorage->store(
            $command->tempFilePath,
            self::UPLOAD_DIRECTORY,
            $storedFilename
        );

        $attachment = new PostAttachment(
            postId: $command->postId,
            authorId: $command->authorId,
            originalFilename: $command->originalFilename,
            storedFilename: $storedFilename,
            mimeType: $command->mimeType,
            size: $command->size
        );

        $this->attachmentRepository->save($attachment);
    }

    private function generateStoredFilename(string $originalFilename): string
    {
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);
        $uniqueId = bin2hex(random_bytes(16));

        return $extension !== '' ? $uniqueId . '.' . $extension : $uniqueId;
    }
}
