<?php

declare(strict_types=1);

namespace App\Vote\UI\Http\Controller;

use App\Comment\Domain\Exception\CommentNotFoundException;
use App\Post\Domain\Exception\PostNotFoundException;
use App\Vote\Application\Command\CastVoteCommand;
use App\Vote\Domain\Exception\InvalidVoteValueException;
use App\Vote\Domain\Exception\SelfVotingException;
use App\Vote\UI\Http\Request\CastVoteRequest;
use App\Vote\UI\Http\Voter\VoteVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CastVoteController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    #[Route('/api/v1/votes', name: 'votes_cast', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function cast(
        #[MapRequestPayload] CastVoteRequest $request
    ): JsonResponse {
        try {
            $user = $this->getUser();

            if ($user === null) {
                return new JsonResponse(
                    ['error' => 'UNAUTHORIZED', 'message' => 'Authentication required'],
                    Response::HTTP_UNAUTHORIZED
                );
            }

            if (!$this->isGranted(VoteVoter::CAST)) {
                return new JsonResponse(
                    ['error' => 'FORBIDDEN', 'message' => 'You do not have permission to cast votes'],
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

            $command = new CastVoteCommand(
                userId: $userId,
                targetType: $request->targetType,
                targetId: $request->targetId,
                value: $request->value
            );

            $this->messageBus->dispatch($command);

            return new JsonResponse(
                ['message' => 'Vote cast successfully'],
                Response::HTTP_OK
            );
        } catch (HandlerFailedException $e) {
            $originalException = $e->getPrevious();

            if ($originalException instanceof SelfVotingException) {
                return new JsonResponse(
                    ['error' => 'SELF_VOTE', 'message' => $originalException->getMessage()],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            if ($originalException instanceof PostNotFoundException || $originalException instanceof CommentNotFoundException) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => $originalException->getMessage()],
                    Response::HTTP_NOT_FOUND
                );
            }

            if ($originalException instanceof InvalidVoteValueException) {
                return new JsonResponse(
                    ['error' => 'INVALID_VOTE_VALUE', 'message' => $originalException->getMessage()],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            return new JsonResponse(
                ['error' => 'VOTE_FAILED', 'message' => 'An error occurred while casting the vote'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception) {
            return new JsonResponse(
                ['error' => 'VOTE_FAILED', 'message' => 'An error occurred while casting the vote'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
