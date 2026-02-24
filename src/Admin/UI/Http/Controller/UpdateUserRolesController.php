<?php

declare(strict_types=1);

namespace App\Admin\UI\Http\Controller;

use App\Admin\Application\Command\UpdateUserRolesCommand;
use App\Admin\Domain\Exception\InvalidRoleException;
use App\Admin\Domain\Exception\UserNotFoundException;
use App\Admin\UI\Http\Request\UpdateUserRolesRequest;
use App\Admin\UI\Http\Voter\AdminVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/admin', name: 'admin_')]
final class UpdateUserRolesController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    #[Route('/users/{id}/roles', name: 'update_user_roles', methods: ['PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function updateRoles(
        string $id,
        #[MapRequestPayload] UpdateUserRolesRequest $request
    ): JsonResponse {
        if (!$this->isGranted(AdminVoter::ADMIN_UPDATE_USER_ROLES)) {
            return new JsonResponse(
                ['error' => 'FORBIDDEN', 'message' => 'Only admins can update user roles'],
                Response::HTTP_FORBIDDEN
            );
        }

        try {
            $command = new UpdateUserRolesCommand(
                userId: $id,
                roles: $request->roles
            );

            $this->messageBus->dispatch($command);

            return new JsonResponse(
                ['message' => 'User roles updated successfully'],
                Response::HTTP_OK
            );
        } catch (HandlerFailedException $e) {
            $originalException = $e->getPrevious();

            if ($originalException instanceof UserNotFoundException) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => $originalException->getMessage()],
                    Response::HTTP_NOT_FOUND
                );
            }

            if ($originalException instanceof InvalidRoleException) {
                return new JsonResponse(
                    ['error' => 'VALIDATION_ERROR', 'message' => $originalException->getMessage()],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            return new JsonResponse(
                ['error' => 'UPDATE_FAILED', 'message' => 'An error occurred while updating user roles'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
