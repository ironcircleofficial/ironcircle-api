<?php

declare(strict_types=1);

namespace App\Circle\UI\Http\Controller;

use App\Circle\Application\Command\DeleteCircleCommand;
use App\Circle\Domain\Exception\CircleNotFoundException;
use App\Circle\Domain\Exception\UnauthorizedCircleAccessException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/circles', name: 'circles_')]
final class DeleteCircleController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
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

            $userId = method_exists($user, 'getId') ? $user->getId() : null;
            
            if ($userId === null) {
                return new JsonResponse(
                    ['error' => 'UNAUTHORIZED', 'message' => 'Invalid user'],
                    Response::HTTP_UNAUTHORIZED
                );
            }

            $command = new DeleteCircleCommand(
                circleId: $id,
                deletedBy: $userId
            );

            $this->messageBus->dispatch($command);

            return new JsonResponse(
                ['message' => 'Circle deleted successfully'],
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
            
            if ($originalException instanceof UnauthorizedCircleAccessException) {
                return new JsonResponse(
                    ['error' => 'FORBIDDEN', 'message' => $originalException->getMessage()],
                    Response::HTTP_FORBIDDEN
                );
            }
            
            return new JsonResponse(
                ['error' => 'DELETE_FAILED', 'message' => 'An error occurred during circle deletion'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'DELETE_FAILED', 'message' => 'An error occurred during circle deletion'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
