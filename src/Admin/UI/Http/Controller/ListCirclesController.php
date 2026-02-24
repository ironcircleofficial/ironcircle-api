<?php

declare(strict_types=1);

namespace App\Admin\UI\Http\Controller;

use App\Admin\Application\Query\ListAllCirclesQuery;
use App\Admin\UI\Http\Transformer\AdminCircleListTransformer;
use App\Admin\UI\Http\Voter\AdminVoter;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/v1/admin', name: 'admin_')]
final class ListCirclesController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly AdminCircleListTransformer $adminCircleListTransformer,
        private readonly Manager $fractal
    ) {
    }

    #[Route('/circles', name: 'list_circles', methods: ['GET'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function list(Request $request): JsonResponse
    {
        if (!$this->isGranted(AdminVoter::ADMIN_LIST_CIRCLES)) {
            return new JsonResponse(
                ['error' => 'FORBIDDEN', 'message' => 'Only admins can list all circles'],
                Response::HTTP_FORBIDDEN
            );
        }

        try {
            $limit = max(1, min(100, (int) $request->query->get('limit', 20)));
            $offset = max(0, (int) $request->query->get('offset', 0));

            $query = new ListAllCirclesQuery(limit: $limit, offset: $offset);
            $envelope = $this->messageBus->dispatch($query);
            $stamp = $envelope->last(HandledStamp::class);

            if (!$stamp instanceof HandledStamp) {
                return new JsonResponse(
                    ['error' => 'FETCH_FAILED', 'message' => 'Failed to retrieve circles'],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            $circleListDTO = $stamp->getResult();
            $resource = new Item($circleListDTO, $this->adminCircleListTransformer, 'circles');
            $data = $this->fractal->createData($resource)->toArray()['data'];

            return new JsonResponse(
                [
                    'circles' => $data['circles']['data'] ?? [],
                    'pagination' => $data['pagination'],
                ],
                Response::HTTP_OK
            );
        } catch (\Exception) {
            return new JsonResponse(
                ['error' => 'FETCH_FAILED', 'message' => 'Failed to retrieve circles'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
