<?php

declare(strict_types=1);

namespace App\AI\UI\Http\Controller;

use App\AI\Application\Query\GetAISummaryByPostIdQuery;
use App\AI\Domain\Exception\AISummaryNotFoundException;
use App\AI\UI\Http\Transformer\AISummaryTransformer;
use App\AI\UI\Http\Voter\AISummaryVoter;
use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Circle\UI\Http\Voter\CircleVoter;
use App\Post\Domain\Repository\PostRepositoryInterface;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

final class GetAISummaryController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly PostRepositoryInterface $postRepository,
        private readonly CircleRepositoryInterface $circleRepository,
        private readonly AISummaryTransformer $aiSummaryTransformer,
        private readonly Manager $fractal
    ) {
    }

    #[Route('/api/v1/posts/{id}/ai-summary', name: 'posts_get_ai_summary', methods: ['GET'])]
    public function __invoke(string $id): JsonResponse
    {
        try {
            $post = $this->postRepository->findById($id);

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
                    ['error' => 'FORBIDDEN', 'message' => 'You do not have permission to view this post'],
                    Response::HTTP_FORBIDDEN
                );
            }

            if (!$this->isGranted(AISummaryVoter::VIEW, $post)) {
                return new JsonResponse(
                    ['error' => 'FORBIDDEN', 'message' => 'Access denied'],
                    Response::HTTP_FORBIDDEN
                );
            }

            if (!$post->isAiSummaryEnabled()) {
                return new JsonResponse(
                    ['error' => 'AI_SUMMARY_NOT_ENABLED', 'message' => 'AI summary is not enabled for this post'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $query = new GetAISummaryByPostIdQuery(postId: $id);
            $envelope = $this->messageBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);

            if (!$handledStamp instanceof HandledStamp) {
                return new JsonResponse(
                    ['error' => 'INTERNAL_ERROR', 'message' => 'Failed to retrieve AI summary'],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            $dto = $handledStamp->getResult();
            $resource = new Item($dto, $this->aiSummaryTransformer, 'aiSummary');
            $transformed = $this->fractal->createData($resource)->toArray();

            return new JsonResponse(
                ['aiSummary' => $transformed['data']],
                Response::HTTP_OK
            );
        } catch (HandlerFailedException $e) {
            $original = $e->getPrevious();

            if ($original instanceof AISummaryNotFoundException) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => $original->getMessage()],
                    Response::HTTP_NOT_FOUND
                );
            }

            return new JsonResponse(
                ['error' => 'INTERNAL_ERROR', 'message' => 'An error occurred while fetching AI summary'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'INTERNAL_ERROR', 'message' => 'An error occurred while fetching AI summary'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
