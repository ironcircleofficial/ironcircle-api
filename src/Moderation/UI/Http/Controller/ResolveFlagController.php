<?php

declare(strict_types=1);

namespace App\Moderation\UI\Http\Controller;

use App\Moderation\Application\Command\ResolveFlagCommand;
use App\Moderation\Domain\Exception\FlagAlreadyResolvedException;
use App\Moderation\Domain\Exception\FlagNotFoundException;
use App\Moderation\UI\Http\Voter\FlagVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ResolveFlagController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    #[Route('/api/v1/moderation/flags/{id}/resolve', name: 'flags_resolve', methods: ['PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function resolve(string $id): JsonResponse
    {
        if (!$this->isGranted(FlagVoter::FLAG_RESOLVE)) {
            return new JsonResponse(
                ['error' => 'FORBIDDEN', 'message' => 'Only moderators and admins can resolve flags'],
                Response::HTTP_FORBIDDEN
            );
        }

        $user = $this->getUser();

        if ($user === null) {
            return new JsonResponse(
                ['error' => 'UNAUTHORIZED', 'message' => 'Authentication required'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        $moderatorId = method_exists($user, 'getId') ? $user->getId() : null;

        if ($moderatorId === null) {
            return new JsonResponse(
                ['error' => 'UNAUTHORIZED', 'message' => 'Invalid user'],
                Response::HTTP_UNAUTHORIZED
            );
        }

        try {
            $command = new ResolveFlagCommand(flagId: $id, moderatorId: $moderatorId);

            $this->messageBus->dispatch($command);

            return new JsonResponse(
                ['message' => 'Flag resolved successfully'],
                Response::HTTP_OK
            );
        } catch (HandlerFailedException $e) {
            $cause = $e->getPrevious();

            if ($cause instanceof FlagNotFoundException) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => $cause->getMessage()],
                    Response::HTTP_NOT_FOUND
                );
            }

            if ($cause instanceof FlagAlreadyResolvedException) {
                return new JsonResponse(
                    ['error' => 'ALREADY_RESOLVED', 'message' => $cause->getMessage()],
                    Response::HTTP_CONFLICT
                );
            }

            return new JsonResponse(
                ['error' => 'RESOLVE_FAILED', 'message' => 'An error occurred while resolving the flag'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
