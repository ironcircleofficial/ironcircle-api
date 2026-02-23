<?php

declare(strict_types=1);

namespace App\Search\UI\Http\Controller;

use App\Search\Application\Query\SearchCirclesQuery;
use App\Search\UI\Http\Transformer\SearchCirclesResultTransformer;
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

final class SearchCirclesController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly SearchCirclesResultTransformer $transformer,
        private readonly Manager $fractal
    ) {
    }

    #[Route('/api/v1/search/circles', name: 'search_circles', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        $q = trim((string) $request->query->get('q', ''));

        if ($q === '') {
            return new JsonResponse(
                ['error' => 'VALIDATION_ERROR', 'message' => 'Search query must not be empty'],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (mb_strlen($q) > 200) {
            return new JsonResponse(
                ['error' => 'VALIDATION_ERROR', 'message' => 'Search query must not exceed 200 characters'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $limit = max(1, min(100, (int) $request->query->get('limit', 20)));
        $offset = max(0, (int) $request->query->get('offset', 0));

        $user = $this->getUser();
        $userId = $user !== null && method_exists($user, 'getId') ? $user->getId() : null;

        try {
            $query = new SearchCirclesQuery(
                query: $q,
                userId: $userId,
                limit: $limit,
                offset: $offset
            );

            $envelope = $this->messageBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);

            if (!$handledStamp instanceof HandledStamp) {
                return new JsonResponse(
                    ['error' => 'SEARCH_FAILED', 'message' => 'Failed to execute circle search'],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            $resultDTO = $handledStamp->getResult();
            $resource = new Item($resultDTO, $this->transformer, 'search_circles');
            $data = $this->fractal->createData($resource)->toArray()['data'];

            return new JsonResponse(
                [
                    'query' => $data['query'],
                    'circles' => $data['circles']['data'] ?? [],
                    'pagination' => $data['pagination'],
                ],
                Response::HTTP_OK
            );
        } catch (HandlerFailedException $e) {
            return new JsonResponse(
                ['error' => 'SEARCH_FAILED', 'message' => 'An error occurred while searching circles'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
