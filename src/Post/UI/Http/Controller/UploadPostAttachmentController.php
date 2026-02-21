<?php

declare(strict_types=1);

namespace App\Post\UI\Http\Controller;

use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Circle\UI\Http\Voter\CircleVoter;
use App\Post\Application\Command\UploadPostAttachmentCommand;
use App\Post\Domain\Repository\PostRepositoryInterface;
use App\Post\UI\Http\Voter\PostAttachmentVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class UploadPostAttachmentController extends AbstractController
{
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/plain',
    ];

    private const MAX_FILE_SIZE = 10 * 1024 * 1024; // 10 MB

    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly PostRepositoryInterface $postRepository,
        private readonly CircleRepositoryInterface $circleRepository
    ) {
    }

    #[Route('/api/v1/posts/{postId}/attachments', name: 'post_attachments_upload', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function upload(string $postId, Request $request): JsonResponse
    {
        $user = $this->getUser();

        if ($user === null) {
            return new JsonResponse(
                ['error' => 'UNAUTHORIZED', 'message' => 'Authentication required'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $post = $this->postRepository->findById($postId);

        if ($post === null) {
            return new JsonResponse(
                ['error' => 'NOT_FOUND', 'message' => 'Post not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $circle = $this->circleRepository->findById($post->getCircleId());

        if ($circle === null) {
            return new JsonResponse(
                ['error' => 'NOT_FOUND', 'message' => 'Circle not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        if (!$this->isGranted(CircleVoter::VIEW, $circle)) {
            return new JsonResponse(
                ['error' => 'FORBIDDEN', 'message' => 'You do not have permission to access this circle'],
                Response::HTTP_FORBIDDEN
            );
        }

        if (!$this->isGranted(PostAttachmentVoter::UPLOAD)) {
            return new JsonResponse(
                ['error' => 'FORBIDDEN', 'message' => 'You do not have permission to upload attachments'],
                Response::HTTP_FORBIDDEN
            );
        }

        $uploadedFile = $request->files->get('file');

        if ($uploadedFile === null) {
            return new JsonResponse(
                ['error' => 'VALIDATION_ERROR', 'message' => 'No file provided. Use form field "file"'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if (!$uploadedFile->isValid()) {
            return new JsonResponse(
                ['error' => 'VALIDATION_ERROR', 'message' => 'File upload failed: ' . $uploadedFile->getErrorMessage()],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $mimeType = $uploadedFile->getMimeType() ?? $uploadedFile->getClientMimeType();

        if (!in_array($mimeType, self::ALLOWED_MIME_TYPES, true)) {
            return new JsonResponse(
                ['error' => 'VALIDATION_ERROR', 'message' => 'File type not allowed. Allowed types: JPEG, PNG, GIF, WebP, PDF, DOC, DOCX, TXT'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($uploadedFile->getSize() > self::MAX_FILE_SIZE) {
            return new JsonResponse(
                ['error' => 'VALIDATION_ERROR', 'message' => 'File size exceeds the maximum limit of 10 MB'],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $userId = method_exists($user, 'getId') ? $user->getId() : null;

        if ($userId === null) {
            return new JsonResponse(
                ['error' => 'UNAUTHORIZED', 'message' => 'Invalid user'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $command = new UploadPostAttachmentCommand(
            postId: $postId,
            authorId: $userId,
            tempFilePath: $uploadedFile->getPathname(),
            originalFilename: $uploadedFile->getClientOriginalName(),
            mimeType: $mimeType,
            size: (int) $uploadedFile->getSize()
        );

        $this->messageBus->dispatch($command);

        return new JsonResponse(
            ['message' => 'Attachment uploaded successfully'],
            Response::HTTP_CREATED
        );
    }
}
