<?php

declare(strict_types=1);

namespace App\Post\UI\Http\Controller;

use App\Post\Domain\Repository\PostAttachmentRepositoryInterface;
use App\Shared\Application\Service\FileStorageInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

final class ServePostAttachmentController extends AbstractController
{
    private const UPLOAD_DIRECTORY = 'post_attachments';

    public function __construct(
        private readonly PostAttachmentRepositoryInterface $attachmentRepository,
        private readonly FileStorageInterface $fileStorage
    ) {
    }

    #[Route('/api/v1/attachments/{id}/download', name: 'post_attachments_download', methods: ['GET'])]
    public function download(string $id): BinaryFileResponse|JsonResponse
    {
        $attachment = $this->attachmentRepository->findById($id);

        if ($attachment === null) {
            return new JsonResponse(
                ['error' => 'NOT_FOUND', 'message' => 'Attachment not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $filePath = $this->fileStorage->getAbsolutePath(
            $attachment->getStoredFilename(),
            self::UPLOAD_DIRECTORY
        );

        if (!file_exists($filePath)) {
            return new JsonResponse(
                ['error' => 'NOT_FOUND', 'message' => 'Attachment file not found on disk'],
                Response::HTTP_NOT_FOUND
            );
        }

        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', $attachment->getMimeType());
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $attachment->getOriginalFilename()
        );

        return $response;
    }
}
