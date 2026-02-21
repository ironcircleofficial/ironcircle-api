<?php

declare(strict_types=1);

namespace App\Comment\UI\Http\Controller;

use App\Comment\Application\Command\DeleteCommentCommand;
use App\Comment\Domain\Exception\CommentNotFoundException;
use App\Comment\Domain\Exception\UnauthorizedCommentAccessException;
use App\Comment\Domain\Repository\CommentRepositoryInterface;
use App\Comment\UI\Http\Voter\CommentVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DeleteCommentController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly CommentRepositoryInterface $commentRepository
    ) {
    }

    #[Route('/api/v1/comments/{id}', name: 'comments_delete', methods: ['DELETE'])]
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

            $comment = $this->commentRepository->findById($id);

            if ($comment === null) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => 'Comment not found'],
                    Response::HTTP_NOT_FOUND
                );
            }

            if (!$this->isGranted(CommentVoter::DELETE, $comment)) {
                return new JsonResponse(
                    ['error' => 'FORBIDDEN', 'message' => 'You do not have permission to delete this comment'],
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

            $command = new DeleteCommentCommand(
                commentId: $id,
                deletedBy: $userId
            );

            $this->messageBus->dispatch($command);

            return new JsonResponse(
                ['message' => 'Comment deleted successfully'],
                Response::HTTP_OK
            );
        } catch (HandlerFailedException $e) {
            $originalException = $e->getPrevious();

            if ($originalException instanceof CommentNotFoundException) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => $originalException->getMessage()],
                    Response::HTTP_NOT_FOUND
                );
            }

            if ($originalException instanceof UnauthorizedCommentAccessException) {
                return new JsonResponse(
                    ['error' => 'FORBIDDEN', 'message' => $originalException->getMessage()],
                    Response::HTTP_FORBIDDEN
                );
            }

            return new JsonResponse(
                ['error' => 'DELETE_FAILED', 'message' => 'An error occurred while deleting the comment'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception) {
            return new JsonResponse(
                ['error' => 'DELETE_FAILED', 'message' => 'An error occurred while deleting the comment'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
