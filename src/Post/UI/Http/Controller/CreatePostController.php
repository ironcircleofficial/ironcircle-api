<?php

declare(strict_types=1);

namespace App\Post\UI\Http\Controller;

use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Circle\UI\Http\Voter\CircleVoter;
use App\Post\Application\Command\CreatePostCommand;
use App\Post\UI\Http\Request\CreatePostRequest;
use App\Post\UI\Http\Voter\PostVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CreatePostController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly CircleRepositoryInterface $circleRepository
    ) {
    }

    #[Route('/api/v1/circles/{circleId}/posts', name: 'posts_create', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function create(
        string $circleId,
        #[MapRequestPayload] CreatePostRequest $request
    ): JsonResponse {
        $user = $this->getUser();

        if ($user === null) {
            return new JsonResponse(
                ['error' => 'UNAUTHORIZED', 'message' => 'Authentication required'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $circle = $this->circleRepository->findById($circleId);

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

        if (!$this->isGranted(PostVoter::CREATE)) {
            return new JsonResponse(
                ['error' => 'FORBIDDEN', 'message' => 'You do not have permission to create posts'],
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

        $command = new CreatePostCommand(
            circleId: $circleId,
            authorId: $userId,
            title: $request->title,
            content: $request->content,
            aiSummaryEnabled: $request->aiSummaryEnabled
        );

        $this->messageBus->dispatch($command);

        return new JsonResponse(
            ['message' => 'Post created successfully'],
            Response::HTTP_CREATED
        );
    }
}
