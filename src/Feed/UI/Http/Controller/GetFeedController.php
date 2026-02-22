<?php

declare(strict_types=1);

namespace App\Feed\UI\Http\Controller;

use App\Feed\Application\Query\GetFeedQuery;
use App\Feed\UI\Http\Transformer\FeedTransformer;
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

final class GetFeedController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly FeedTransformer $feedTransformer,
        private readonly Manager $fractal
    ) {
    }

    #[Route('/api/v1/feed', name: 'feed_global', methods: ['GET'])]
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $limit = max(1, min(100, (int) $request->query->get('limit', 20)));
            $offset = max(0, (int) $request->query->get('offset', 0));

            $user = $this->getUser();
            $userId = $user !== null && method_exists($user, 'getId') ? $user->getId() : null;

            $query = new GetFeedQuery(
                userId: $userId,
                limit: $limit,
                offset: $offset
            );

            $envelope = $this->messageBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);

            if (!$handledStamp instanceof HandledStamp) {
                return new JsonResponse(
                    ['error' => 'FETCH_FAILED', 'message' => 'Failed to retrieve feed'],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            $feedDTO = $handledStamp->getResult();
            $resource = new Item($feedDTO, $this->feedTransformer, 'feed');
            $data = $this->fractal->createData($resource)->toArray()['data'];

            return new JsonResponse(
                [
                    'posts' => $data['posts']['data'] ?? [],
                    'pagination' => $data['pagination'],
                ],
                Response::HTTP_OK
            );
        } catch (HandlerFailedException $e) {
            return new JsonResponse(
                ['error' => 'FETCH_FAILED', 'message' => 'An error occurred while fetching the feed'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'FETCH_FAILED', 'message' => 'An error occurred while fetching the feed'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
