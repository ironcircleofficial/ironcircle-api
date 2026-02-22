<?php

declare(strict_types=1);

namespace App\Moderation\UI\Http\Controller;

use App\Moderation\Application\Query\ListPendingFlagsQuery;
use App\Moderation\UI\Http\Transformer\FlagListTransformer;
use App\Moderation\UI\Http\Voter\FlagVoter;
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
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class ListPendingFlagsController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly FlagListTransformer $flagListTransformer,
        private readonly Manager $fractal
    ) {
    }

    #[Route('/api/v1/moderation/flags', name: 'flags_list_pending', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function list(Request $request): JsonResponse
    {
        if (!$this->isGranted(FlagVoter::FLAG_LIST)) {
            return new JsonResponse(
                ['error' => 'FORBIDDEN', 'message' => 'Only moderators and admins can list flags'],
                Response::HTTP_FORBIDDEN
            );
        }

        try {
            $limit  = max(1, min(100, (int) $request->query->get('limit', 20)));
            $offset = max(0, (int) $request->query->get('offset', 0));

            $query    = new ListPendingFlagsQuery(limit: $limit, offset: $offset);
            $envelope = $this->messageBus->dispatch($query);
            $stamp    = $envelope->last(HandledStamp::class);

            if (!$stamp instanceof HandledStamp) {
                return new JsonResponse(
                    ['error' => 'FETCH_FAILED', 'message' => 'Failed to retrieve flags'],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            $flagListDTO = $stamp->getResult();
            $resource    = new Item($flagListDTO, $this->flagListTransformer, 'flags');
            $data        = $this->fractal->createData($resource)->toArray()['data'];

            return new JsonResponse(
                [
                    'flags'      => $data['flags']['data'] ?? [],
                    'pagination' => $data['pagination'],
                ],
                Response::HTTP_OK
            );
        } catch (HandlerFailedException) {
            return new JsonResponse(
                ['error' => 'FETCH_FAILED', 'message' => 'Failed to retrieve flags'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
