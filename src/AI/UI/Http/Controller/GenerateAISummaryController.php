<?php

declare(strict_types=1);

namespace App\AI\UI\Http\Controller;

use App\AI\Application\Command\GenerateAISummaryCommand;
use App\AI\Application\Query\GetAISummaryByPostIdQuery;
use App\AI\Domain\Exception\AISummaryGenerationException;
use App\AI\Domain\Exception\AISummaryNotEnabledException;
use App\AI\UI\Http\Transformer\AISummaryTransformer;
use App\AI\UI\Http\Voter\AISummaryVoter;
use App\Post\Domain\Exception\PostNotFoundException;
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
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class GenerateAISummaryController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly PostRepositoryInterface $postRepository,
        private readonly AISummaryTransformer $aiSummaryTransformer,
        private readonly Manager $fractal
    ) {
    }

    #[Route('/api/v1/posts/{id}/ai-summary/generate', name: 'posts_generate_ai_summary', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function __invoke(string $id): JsonResponse
    {
        try {
            $user = $this->getUser();

            if ($user === null) {
                return new JsonResponse(
                    ['error' => 'UNAUTHORIZED', 'message' => 'Authentication required'],
                    Response::HTTP_UNAUTHORIZED
                );
            }

            $post = $this->postRepository->findById($id);

            if ($post === null) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => 'Post not found'],
                    Response::HTTP_NOT_FOUND
                );
            }

            if (!$this->isGranted(AISummaryVoter::GENERATE, $post)) {
                return new JsonResponse(
                    ['error' => 'FORBIDDEN', 'message' => 'You do not have permission to generate AI summaries'],
                    Response::HTTP_FORBIDDEN
                );
            }

            if (!$post->isAiSummaryEnabled()) {
                return new JsonResponse(
                    ['error' => 'AI_SUMMARY_NOT_ENABLED', 'message' => 'AI summary is not enabled for this post'],
                    Response::HTTP_BAD_REQUEST
                );
            }

            $userId = method_exists($user, 'getId') ? $user->getId() : null;

            if ($userId === null) {
                return new JsonResponse(
                    ['error' => 'UNAUTHORIZED', 'message' => 'Invalid user'],
                    Response::HTTP_UNAUTHORIZED
                );
            }

            $command = new GenerateAISummaryCommand(
                postId: $id,
                requestedBy: $userId
            );

            $this->messageBus->dispatch($command);

            $query = new GetAISummaryByPostIdQuery(postId: $id);
            $envelope = $this->messageBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);

            if (!$handledStamp instanceof HandledStamp) {
                return new JsonResponse(
                    ['error' => 'GENERATION_FAILED', 'message' => 'Failed to generate AI summary'],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            $dto = $handledStamp->getResult();
            $resource = new Item($dto, $this->aiSummaryTransformer, 'aiSummary');
            $transformed = $this->fractal->createData($resource)->toArray();

            return new JsonResponse(
                ['aiSummary' => $transformed['data']],
                Response::HTTP_CREATED
            );
        } catch (HandlerFailedException $e) {
            $original = $e->getPrevious();

            if ($original instanceof PostNotFoundException) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => $original->getMessage()],
                    Response::HTTP_NOT_FOUND
                );
            }

            if ($original instanceof AISummaryNotEnabledException) {
                return new JsonResponse(
                    ['error' => 'AI_SUMMARY_NOT_ENABLED', 'message' => $original->getMessage()],
                    Response::HTTP_BAD_REQUEST
                );
            }

            if ($original instanceof AISummaryGenerationException) {
                return new JsonResponse(
                    ['error' => 'GENERATION_FAILED', 'message' => $original->getMessage()],
                    Response::HTTP_SERVICE_UNAVAILABLE
                );
            }

            return new JsonResponse(
                ['error' => 'GENERATION_FAILED', 'message' => 'An error occurred during AI summary generation'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'GENERATION_FAILED', 'message' => 'An error occurred during AI summary generation'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
