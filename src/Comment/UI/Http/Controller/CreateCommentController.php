<?php

declare(strict_types=1);

namespace App\Comment\UI\Http\Controller;

use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Circle\UI\Http\Voter\CircleVoter;
use App\Comment\Application\Command\CreateCommentCommand;
use App\Comment\Domain\Exception\UnauthorizedCommentAccessException;
use App\Comment\UI\Http\Request\CreateCommentRequest;
use App\Comment\UI\Http\Voter\CommentVoter;
use App\Post\Domain\Exception\PostNotFoundException;
use App\Post\Domain\Repository\PostRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CreateCommentController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly PostRepositoryInterface $postRepository,
        private readonly CircleRepositoryInterface $circleRepository
    ) {
    }

    #[Route('/api/v1/posts/{postId}/comments', name: 'comments_create', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function create(
        string $postId,
        #[MapRequestPayload] CreateCommentRequest $request
    ): JsonResponse {
        try {
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

            if (!$this->isGranted(CommentVoter::CREATE)) {
                return new JsonResponse(
                    ['error' => 'FORBIDDEN', 'message' => 'You do not have permission to create comments'],
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

            $command = new CreateCommentCommand(
                postId: $postId,
                authorId: $userId,
                content: $request->content,
                parentCommentId: $request->parentCommentId
            );

            $this->messageBus->dispatch($command);

            return new JsonResponse(
                ['message' => 'Comment created successfully'],
                Response::HTTP_CREATED
            );
        } catch (HandlerFailedException $e) {
            $originalException = $e->getPrevious();

            if ($originalException instanceof PostNotFoundException) {
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

            if ($originalException instanceof \InvalidArgumentException) {
                return new JsonResponse(
                    ['error' => 'INVALID_ARGUMENT', 'message' => $originalException->getMessage()],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            return new JsonResponse(
                ['error' => 'CREATE_FAILED', 'message' => 'An error occurred while creating the comment'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception) {
            return new JsonResponse(
                ['error' => 'CREATE_FAILED', 'message' => 'An error occurred while creating the comment'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
