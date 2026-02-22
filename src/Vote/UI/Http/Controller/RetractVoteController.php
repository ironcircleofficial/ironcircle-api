<?php

declare(strict_types=1);

namespace App\Vote\UI\Http\Controller;

use App\Vote\Application\Command\RetractVoteCommand;
use App\Vote\Domain\Exception\VoteNotFoundException;
use App\Vote\UI\Http\Voter\VoteVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class RetractVoteController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    #[Route('/api/v1/votes/{targetType}/{targetId}', name: 'votes_retract', methods: ['DELETE'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function retract(string $targetType, string $targetId): JsonResponse
    {
        try {
            if (!in_array($targetType, ['post', 'comment'], true)) {
                return new JsonResponse(
                    ['error' => 'INVALID_TARGET_TYPE', 'message' => 'Target type must be "post" or "comment"'],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $user = $this->getUser();

            if ($user === null) {
                return new JsonResponse(
                    ['error' => 'UNAUTHORIZED', 'message' => 'Authentication required'],
                    Response::HTTP_UNAUTHORIZED
                );
            }

            if (!$this->isGranted(VoteVoter::RETRACT)) {
                return new JsonResponse(
                    ['error' => 'FORBIDDEN', 'message' => 'You do not have permission to retract votes'],
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

            $command = new RetractVoteCommand(
                userId: $userId,
                targetType: $targetType,
                targetId: $targetId
            );

            $this->messageBus->dispatch($command);

            return new JsonResponse(
                ['message' => 'Vote retracted successfully'],
                Response::HTTP_OK
            );
        } catch (HandlerFailedException $e) {
            $originalException = $e->getPrevious();

            if ($originalException instanceof VoteNotFoundException) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => $originalException->getMessage()],
                    Response::HTTP_NOT_FOUND
                );
            }

            return new JsonResponse(
                ['error' => 'RETRACT_FAILED', 'message' => 'An error occurred while retracting the vote'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception) {
            return new JsonResponse(
                ['error' => 'RETRACT_FAILED', 'message' => 'An error occurred while retracting the vote'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
