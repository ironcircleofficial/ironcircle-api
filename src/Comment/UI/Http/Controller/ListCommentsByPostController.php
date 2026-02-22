<?php

declare(strict_types=1);

namespace App\Comment\UI\Http\Controller;

use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Circle\UI\Http\Voter\CircleVoter;
use App\Comment\Application\Query\ListCommentsByPostQuery;
use App\Comment\UI\Http\Transformer\CommentListTransformer;
use App\Post\Domain\Repository\PostRepositoryInterface;
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

final class ListCommentsByPostController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly PostRepositoryInterface $postRepository,
        private readonly CircleRepositoryInterface $circleRepository,
        private readonly CommentListTransformer $commentListTransformer,
        private readonly Manager $fractal
    ) {
    }

    #[Route('/api/v1/posts/{postId}/comments', name: 'comments_list_by_post', methods: ['GET'])]
    public function list(string $postId, Request $request): JsonResponse
    {
        try {
            $post = $this->postRepository->findById($postId);

            if ($post === null) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => 'Post not found'],
                    Response::HTTP_NOT_FOUND
                );
            }

            $circle = $this->circleRepository->findById($post->getCircleId());

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

            $query = new ListCommentsByPostQuery(
                postId: $postId,
                limit: $limit,
                offset: $offset
            );

            $envelope = $this->messageBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);

            if (!$handledStamp instanceof HandledStamp) {
                return new JsonResponse(
                    ['error' => 'FETCH_FAILED', 'message' => 'Failed to retrieve comments'],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            $commentListDTO = $handledStamp->getResult();
            $resource = new Item($commentListDTO, $this->commentListTransformer, 'comments');
            $data = $this->fractal->createData($resource)->toArray()['data'];

            return new JsonResponse(
                [
                    'comments'   => $data['comments']['data'] ?? [],
                    'pagination' => $data['pagination'],
                ],
                Response::HTTP_OK
            );
        } catch (HandlerFailedException) {
            return new JsonResponse(
                ['error' => 'FETCH_FAILED', 'message' => 'Failed to retrieve comments'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception) {
            return new JsonResponse(
                ['error' => 'FETCH_FAILED', 'message' => 'An error occurred while retrieving comments'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
