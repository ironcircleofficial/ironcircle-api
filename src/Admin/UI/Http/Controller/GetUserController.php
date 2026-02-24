<?php

declare(strict_types=1);

namespace App\Admin\UI\Http\Controller;

use App\Admin\Application\Query\GetUserByIdQuery;
use App\Admin\Domain\Exception\UserNotFoundException;
use App\Admin\UI\Http\Transformer\AdminUserTransformer;
use App\Admin\UI\Http\Voter\AdminVoter;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/admin', name: 'admin_')]
final class GetUserController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly AdminUserTransformer $adminUserTransformer,
        private readonly Manager $fractal
    ) {
    }

    #[Route('/users/{id}', name: 'get_user', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function get(string $id): JsonResponse
    {
        if (!$this->isGranted(AdminVoter::ADMIN_VIEW_USER)) {
            return new JsonResponse(
                ['error' => 'FORBIDDEN', 'message' => 'Only admins can view user details'],
                Response::HTTP_FORBIDDEN
            );
        }

        try {
            $query = new GetUserByIdQuery(userId: $id);
            $envelope = $this->messageBus->dispatch($query);
            $stamp = $envelope->last(HandledStamp::class);

            if (!$stamp instanceof HandledStamp) {
                return new JsonResponse(
                    ['error' => 'FETCH_FAILED', 'message' => 'Failed to retrieve user'],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            $userDTO = $stamp->getResult();
            $resource = new Item($userDTO, $this->adminUserTransformer, 'user');
            $data = $this->fractal->createData($resource)->toArray();

            return new JsonResponse(
                ['user' => $data['data']],
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

            return new JsonResponse(
                ['error' => 'FETCH_FAILED', 'message' => 'Failed to retrieve user'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
