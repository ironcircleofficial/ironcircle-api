<?php

declare(strict_types=1);

namespace App\Post\UI\Http\Controller;

use App\Circle\Domain\Repository\CircleRepositoryInterface;
use App\Circle\UI\Http\Voter\CircleVoter;
use App\Post\Application\Query\ListPostAttachmentsByPostQuery;
use App\Post\Domain\Repository\PostRepositoryInterface;
use App\Post\UI\Http\Transformer\PostAttachmentTransformer;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

final class ListPostAttachmentsController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly PostRepositoryInterface $postRepository,
        private readonly CircleRepositoryInterface $circleRepository,
        private readonly PostAttachmentTransformer $postAttachmentTransformer,
        private readonly Manager $fractal
    ) {
    }

    #[Route('/api/v1/posts/{postId}/attachments', name: 'post_attachments_list', methods: ['GET'])]
    public function list(string $postId): JsonResponse
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
                    ['error' => 'FORBIDDEN', 'message' => 'You do not have permission to view this post'],
                    Response::HTTP_FORBIDDEN
                );
            }

            $query = new ListPostAttachmentsByPostQuery(postId: $postId);
            $envelope = $this->messageBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);

            if (!$handledStamp instanceof HandledStamp) {
                return new JsonResponse(
                    ['error' => 'FETCH_FAILED', 'message' => 'Failed to retrieve attachments'],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            $attachmentDTOs = $handledStamp->getResult();
            $resource = new Collection($attachmentDTOs, $this->postAttachmentTransformer, 'attachments');
            $transformed = $this->fractal->createData($resource)->toArray();

            return new JsonResponse(
                ['attachments' => $transformed['data']],
                Response::HTTP_OK
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'FETCH_FAILED', 'message' => 'An error occurred while fetching attachments'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
