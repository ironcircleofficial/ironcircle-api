<?php

declare(strict_types=1);

namespace App\Post\UI\Http\Controller;

use App\Post\Application\Command\DeletePostAttachmentCommand;
use App\Post\Domain\Exception\PostAttachmentNotFoundException;
use App\Post\Domain\Exception\UnauthorizedPostAccessException;
use App\Post\Domain\Repository\PostAttachmentRepositoryInterface;
use App\Post\UI\Http\Voter\PostAttachmentVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DeletePostAttachmentController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly PostAttachmentRepositoryInterface $attachmentRepository
    ) {
    }

    #[Route('/api/v1/attachments/{id}', name: 'post_attachments_delete', methods: ['DELETE'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(string $id): JsonResponse
    {
        try {
            $user = $this->getUser();

            if ($user === null) {
                return new JsonResponse(
                    ['error' => 'UNAUTHORIZED', 'message' => 'Authentication required'],
                    Response::HTTP_UNAUTHORIZED
                );
            }

            $attachment = $this->attachmentRepository->findById($id);

            if ($attachment === null) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => 'Attachment not found'],
                    Response::HTTP_NOT_FOUND
                );
            }

            if (!$this->isGranted(PostAttachmentVoter::DELETE, $attachment)) {
                return new JsonResponse(
                    ['error' => 'FORBIDDEN', 'message' => 'You do not have permission to delete this attachment'],
                    Response::HTTP_FORBIDDEN
                );
            }

            $userId = method_exists($user, 'getId') ? $user->getId() : null;

            if ($userId === null) {
                return new JsonResponse(
                    ['error' => 'UNAUTHORIZED', 'message' => 'Invalid user'],
                    Response::HTTP_UNAUTHORIZED
                );
            }

            $command = new DeletePostAttachmentCommand(
                attachmentId: $id,
                deletedBy: $userId
            );

            $this->messageBus->dispatch($command);

            return new JsonResponse(
                ['message' => 'Attachment deleted successfully'],
                Response::HTTP_OK
            );
        } catch (HandlerFailedException $e) {
            $originalException = $e->getPrevious();

            if ($originalException instanceof PostAttachmentNotFoundException) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => $originalException->getMessage()],
                    Response::HTTP_NOT_FOUND
                );
            }

            if ($originalException instanceof UnauthorizedPostAccessException) {
                return new JsonResponse(
                    ['error' => 'FORBIDDEN', 'message' => $originalException->getMessage()],
                    Response::HTTP_FORBIDDEN
                );
            }

            return new JsonResponse(
                ['error' => 'DELETE_FAILED', 'message' => 'An error occurred during attachment deletion'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'DELETE_FAILED', 'message' => 'An error occurred during attachment deletion'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
