<?php

declare(strict_types=1);

namespace App\Admin\UI\Http\Controller;

use App\Admin\Application\Command\AdminForceDeleteCircleCommand;
use App\Admin\UI\Http\Voter\AdminVoter;
use App\Circle\Domain\Exception\CircleNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/admin', name: 'admin_')]
final class ForceDeleteCircleController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    #[Route('/circles/{id}', name: 'force_delete_circle', methods: ['DELETE'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(string $id): JsonResponse
    {
        if (!$this->isGranted(AdminVoter::ADMIN_FORCE_DELETE_CIRCLE)) {
            return new JsonResponse(
                ['error' => 'FORBIDDEN', 'message' => 'Only admins can force-delete circles'],
                Response::HTTP_FORBIDDEN
            );
        }

        try {
            $user = $this->getUser();

            if ($user === null) {
                return new JsonResponse(
                    ['error' => 'UNAUTHORIZED', 'message' => 'Authentication required'],
                    Response::HTTP_UNAUTHORIZED
                );
            }

            $userId = method_exists($user, 'getId') ? $user->getId() : null;

            if ($userId === null) {
                return new JsonResponse(
                    ['error' => 'UNAUTHORIZED', 'message' => 'Invalid user'],
                    Response::HTTP_UNAUTHORIZED
                );
            }

            $command = new AdminForceDeleteCircleCommand(
                circleId: $id,
                deletedBy: $userId
            );

            $this->messageBus->dispatch($command);

            return new JsonResponse(
                ['message' => 'Circle force-deleted successfully'],
                Response::HTTP_OK
            );
        } catch (HandlerFailedException $e) {
            $originalException = $e->getPrevious();

            if ($originalException instanceof CircleNotFoundException) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => $originalException->getMessage()],
                    Response::HTTP_NOT_FOUND
                );
            }

            return new JsonResponse(
                ['error' => 'DELETE_FAILED', 'message' => 'An error occurred during circle deletion'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
