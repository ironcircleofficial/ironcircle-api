<?php

declare(strict_types=1);

namespace App\Post\UI\Http\Controller;

use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Circle\UI\Http\Voter\CircleVoter;
use App\Post\Application\Query\ListPostsByCircleQuery;
use App\Post\UI\Http\Transformer\PostListTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

final class ListPostsByCircleController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly CircleRepositoryInterface $circleRepository,
        private readonly PostListTransformer $postListTransformer,
        private readonly Manager $fractal
    ) {
    }

    #[Route('/api/v1/circles/{circleId}/posts', name: 'posts_list_by_circle', methods: ['GET'])]
    public function list(string $circleId, Request $request): JsonResponse
    {
        try {
            $circle = $this->circleRepository->findById($circleId);

            if ($circle === null) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => 'Circle not found'],
                    Response::HTTP_NOT_FOUND
                );
            }

            if (!$this->isGranted(CircleVoter::VIEW, $circle)) {
                return new JsonResponse(
                    ['error' => 'FORBIDDEN', 'message' => 'You do not have permission to access this circle'],
                    Response::HTTP_FORBIDDEN
                );
            }

            $limit = max(1, min(100, (int) $request->query->get('limit', 20)));
            $offset = max(0, (int) $request->query->get('offset', 0));

            $query = new ListPostsByCircleQuery(
                circleId: $circleId,
                limit: $limit,
                offset: $offset
            );

            $envelope = $this->messageBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);

            if (!$handledStamp instanceof HandledStamp) {
                return new JsonResponse(
                    ['error' => 'FETCH_FAILED', 'message' => 'Failed to retrieve posts'],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            $postListDTO = $handledStamp->getResult();
            $resource = new Item($postListDTO, $this->postListTransformer, 'posts');
            $data = $this->fractal->createData($resource)->toArray()['data'];

            return new JsonResponse(
                [
                    'posts'      => $data['posts']['data'] ?? [],
                    'pagination' => $data['pagination'],
                ],
                Response::HTTP_OK
            );
        } catch (HandlerFailedException $e) {
            return new JsonResponse(
                ['error' => 'FETCH_FAILED', 'message' => 'An error occurred while fetching posts'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'FETCH_FAILED', 'message' => 'An error occurred while fetching posts'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
