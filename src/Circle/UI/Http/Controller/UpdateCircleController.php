<?php

declare(strict_types=1);

namespace App\Circle\UI\Http\Controller;

use App\Circle\Application\Command\UpdateCircleCommand;
use App\Circle\Application\Query\GetCircleByIdQuery;
use App\Circle\Domain\Exception\CircleNotFoundException;
use App\Circle\Domain\Exception\UnauthorizedCircleAccessException;
use App\Circle\UI\Http\Request\UpdateCircleRequest;
use App\Circle\UI\Http\Transformer\CircleTransformer;
use App\Circle\UI\Http\Voter\CircleVoter;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/circles', name: 'circles_')]
final class UpdateCircleController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly CircleTransformer $circleTransformer,
        private readonly Manager $fractal
    ) {
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function update(
        string $id,
        #[MapRequestPayload] UpdateCircleRequest $request
    ): JsonResponse {
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

            $command = new UpdateCircleCommand(
                circleId: $id,
                name: $request->name,
                description: $request->description,
                updatedBy: $userId
            );

            $this->messageBus->dispatch($command);

            $query = new GetCircleByIdQuery(id: $id);
            $envelope = $this->messageBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);

            if (!$handledStamp instanceof HandledStamp) {
                return new JsonResponse(
                    ['error' => 'UPDATE_FAILED', 'message' => 'Circle update failed'],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            $circleDTO = $handledStamp->getResult();
            $resource = new Item($circleDTO, $this->circleTransformer, 'circle');
            $transformedCircle = $this->fractal->createData($resource)->toArray();

            return new JsonResponse(
                [
                    'message' => 'Circle updated successfully',
                    'circle' => $transformedCircle['data']
                ],
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
                ['error' => 'UPDATE_FAILED', 'message' => 'An error occurred during circle update'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'UPDATE_FAILED', 'message' => 'An error occurred during circle update'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
