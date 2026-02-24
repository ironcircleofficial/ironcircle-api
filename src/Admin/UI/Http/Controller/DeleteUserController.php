<?php

declare(strict_types=1);

namespace App\Admin\UI\Http\Controller;

use App\Admin\Application\Command\AdminDeleteUserCommand;
use App\Admin\Domain\Exception\CannotDeleteAdminException;
use App\Admin\Domain\Exception\UserNotFoundException;
use App\Admin\UI\Http\Voter\AdminVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/admin', name: 'admin_')]
final class DeleteUserController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    #[Route('/users/{id}', name: 'delete_user', methods: ['DELETE'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function delete(string $id): JsonResponse
    {
        if (!$this->isGranted(AdminVoter::ADMIN_DELETE_USER)) {
            return new JsonResponse(
                ['error' => 'FORBIDDEN', 'message' => 'Only admins can delete users'],
                Response::HTTP_FORBIDDEN
            );
        }

        try {
            $command = new AdminDeleteUserCommand(userId: $id);
            $this->messageBus->dispatch($command);

            return new JsonResponse(
                ['message' => 'User deleted successfully'],
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

            if ($originalException instanceof CannotDeleteAdminException) {
                return new JsonResponse(
                    ['error' => 'FORBIDDEN', 'message' => $originalException->getMessage()],
                    Response::HTTP_FORBIDDEN
                );
            }

            return new JsonResponse(
                ['error' => 'DELETE_FAILED', 'message' => 'An error occurred while deleting the user'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
