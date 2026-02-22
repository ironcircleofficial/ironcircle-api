<?php

declare(strict_types=1);

namespace App\Moderation\UI\Http\Controller;

use App\Comment\Domain\Exception\CommentNotFoundException;
use App\Moderation\Application\Command\FlagContentCommand;
use App\Moderation\Domain\Exception\DuplicateFlagException;
use App\Moderation\UI\Http\Request\FlagContentRequest;
use App\Moderation\UI\Http\Voter\FlagVoter;
use App\Post\Domain\Exception\PostNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class FlagContentController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    #[Route('/api/v1/flags', name: 'flags_create', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function flag(
        #[MapRequestPayload] FlagContentRequest $request
    ): JsonResponse {
        $user = $this->getUser();

        if ($user === null) {
            return new JsonResponse(
                ['error' => 'UNAUTHORIZED', 'message' => 'Authentication required'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        if (!$this->isGranted(FlagVoter::FLAG_CREATE)) {
            return new JsonResponse(
                ['error' => 'FORBIDDEN', 'message' => 'You do not have permission to flag content'],
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

        try {
            $command = new FlagContentCommand(
                reporterId: $userId,
                targetType: $request->targetType,
                targetId: $request->targetId,
                reason: $request->reason
            );

            $this->messageBus->dispatch($command);

            return new JsonResponse(
                ['message' => 'Content flagged successfully'],
                Response::HTTP_CREATED
            );
        } catch (HandlerFailedException $e) {
            $cause = $e->getPrevious();

            if ($cause instanceof DuplicateFlagException) {
                return new JsonResponse(
                    ['error' => 'DUPLICATE_FLAG', 'message' => $cause->getMessage()],
                    Response::HTTP_CONFLICT
                );
            }

            if ($cause instanceof PostNotFoundException || $cause instanceof CommentNotFoundException) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => $cause->getMessage()],
                    Response::HTTP_NOT_FOUND
                );
            }

            return new JsonResponse(
                ['error' => 'FLAG_FAILED', 'message' => 'An error occurred while flagging the content'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
