<?php

declare(strict_types=1);

namespace App\Circle\UI\Http\Controller;

use App\Circle\Application\Query\ListCirclesQuery;
use App\Circle\UI\Http\Transformer\CircleListTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/v1/circles', name: 'circles_')]
final class ListCirclesController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly CircleListTransformer $circleListTransformer,
        private readonly Manager $fractal
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        try {
            $limit = max(1, min(100, (int) $request->query->get('limit', 20)));
            $offset = max(0, (int) $request->query->get('offset', 0));
            $publicOnly = $request->query->getBoolean('public_only', false);

            $user = $this->getUser();
            if (!$user && !$publicOnly) {
                $publicOnly = true;
            }

            $query = new ListCirclesQuery(
                limit: $limit,
                offset: $offset,
                publicOnly: $publicOnly
            );

            $envelope = $this->messageBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);

            if (!$handledStamp instanceof HandledStamp) {
                return new JsonResponse(
                    ['error' => 'FETCH_FAILED', 'message' => 'Failed to fetch circles'],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            $circleListDTO = $handledStamp->getResult();
            $resource = new Item($circleListDTO, $this->circleListTransformer, 'circles');
            $transformedList = $this->fractal->createData($resource)->toArray();

            return new JsonResponse(
                $transformedList['data'],
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'FETCH_FAILED', 'message' => 'An error occurred while fetching circles'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
