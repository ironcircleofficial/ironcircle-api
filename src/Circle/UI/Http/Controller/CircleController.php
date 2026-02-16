<?php

declare(strict_types=1);

namespace App\Circle\UI\Http\Controller;

use App\Circle\Application\Command\CreateCircleCommand;
use App\Circle\Application\Query\GetCircleBySlugQuery;
use App\Circle\Domain\Exception\CircleAlreadyExistsException;
use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Circle\UI\Http\Request\CreateCircleRequest;
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
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/api/v1/circles', name: 'circles_')]
final class CircleController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly CircleRepositoryInterface $circleRepository,
        private readonly CircleTransformer $circleTransformer,
        private readonly Manager $fractal,
        private readonly SluggerInterface $slugger
    ) {
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[IsGranted(CircleVoter::CREATE)]
    public function create(
        #[MapRequestPayload] CreateCircleRequest $request
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

            $command = new CreateCircleCommand(
                name: $request->name,
                description: $request->description,
                visibility: $request->visibility,
                creatorId: $userId
            );

            $this->messageBus->dispatch($command);

            $slug = $this->slugger->slug($request->name)->lower()->toString();
            $query = new GetCircleBySlugQuery(slug: $slug);
            
            $envelope = $this->messageBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);

            if (!$handledStamp instanceof HandledStamp) {
                return new JsonResponse(
                    ['error' => 'CREATION_FAILED', 'message' => 'Circle creation failed'],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            $circleDTO = $handledStamp->getResult();
            $resource = new Item($circleDTO, $this->circleTransformer, 'circle');
            $transformedCircle = $this->fractal->createData($resource)->toArray();

            return new JsonResponse(
                [
                    'message' => 'Circle created successfully',
                    'circle' => $transformedCircle['data']
                ],
                Response::HTTP_CREATED
            );
        } catch (HandlerFailedException $e) {
            $originalException = $e->getPrevious();
            
            if ($originalException instanceof CircleAlreadyExistsException) {
                return new JsonResponse(
                    ['error' => 'CIRCLE_ALREADY_EXISTS', 'message' => $originalException->getMessage()],
                    Response::HTTP_CONFLICT
                );
            }
            
            return new JsonResponse(
                ['error' => 'CREATION_FAILED', 'message' => 'An error occurred during circle creation'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (CircleAlreadyExistsException $e) {
            return new JsonResponse(
                ['error' => 'CIRCLE_ALREADY_EXISTS', 'message' => $e->getMessage()],
                Response::HTTP_CONFLICT
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'CREATION_FAILED', 'message' => 'An error occurred during circle creation'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/{id}', name: 'get_by_id', methods: ['GET'])]
    public function getById(string $id): JsonResponse
    {
        try {
            $circle = $this->circleRepository->findById($id);

            if ($circle === null) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => 'Circle not found'],
                    Response::HTTP_NOT_FOUND
                );
            }

            if (!$this->isGranted(CircleVoter::VIEW, $circle)) {
                return new JsonResponse(
                    ['error' => 'FORBIDDEN', 'message' => 'You do not have permission to view this circle'],
                    Response::HTTP_FORBIDDEN
                );
            }

            $query = new \App\Circle\Application\Query\GetCircleByIdQuery(id: $id);
            $envelope = $this->messageBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);

            if (!$handledStamp instanceof HandledStamp) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => 'Circle not found'],
                    Response::HTTP_NOT_FOUND
                );
            }

            $circleDTO = $handledStamp->getResult();
            $resource = new Item($circleDTO, $this->circleTransformer, 'circle');
            $transformedCircle = $this->fractal->createData($resource)->toArray();

            return new JsonResponse(
                ['circle' => $transformedCircle['data']],
                Response::HTTP_OK
            );
        } catch (HandlerFailedException $e) {
            $originalException = $e->getPrevious();
            
            if ($originalException instanceof \App\Circle\Domain\Exception\CircleNotFoundException) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => $originalException->getMessage()],
                    Response::HTTP_NOT_FOUND
                );
            }
            
            return new JsonResponse(
                ['error' => 'FETCH_FAILED', 'message' => 'An error occurred while fetching circle'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'FETCH_FAILED', 'message' => 'An error occurred while fetching circle'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/slug/{slug}', name: 'get_by_slug', methods: ['GET'])]
    public function getBySlug(string $slug): JsonResponse
    {
        try {
            $circle = $this->circleRepository->findBySlug($slug);

            if ($circle === null) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => 'Circle not found'],
                    Response::HTTP_NOT_FOUND
                );
            }

            if (!$this->isGranted(CircleVoter::VIEW, $circle)) {
                return new JsonResponse(
                    ['error' => 'FORBIDDEN', 'message' => 'You do not have permission to view this circle'],
                    Response::HTTP_FORBIDDEN
                );
            }

            $query = new GetCircleBySlugQuery(slug: $slug);
            $envelope = $this->messageBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);

            if (!$handledStamp instanceof HandledStamp) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => 'Circle not found'],
                    Response::HTTP_NOT_FOUND
                );
            }

            $circleDTO = $handledStamp->getResult();
            $resource = new Item($circleDTO, $this->circleTransformer, 'circle');
            $transformedCircle = $this->fractal->createData($resource)->toArray();

            return new JsonResponse(
                ['circle' => $transformedCircle['data']],
                Response::HTTP_OK
            );
        } catch (HandlerFailedException $e) {
            $originalException = $e->getPrevious();
            
            if ($originalException instanceof \App\Circle\Domain\Exception\CircleNotFoundException) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => $originalException->getMessage()],
                    Response::HTTP_NOT_FOUND
                );
            }
            
            return new JsonResponse(
                ['error' => 'FETCH_FAILED', 'message' => 'An error occurred while fetching circle'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'FETCH_FAILED', 'message' => 'An error occurred while fetching circle'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
