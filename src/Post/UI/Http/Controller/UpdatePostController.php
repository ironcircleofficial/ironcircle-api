<?php

declare(strict_types=1);

namespace App\Post\UI\Http\Controller;

use App\Post\Application\Command\UpdatePostCommand;
use App\Post\Application\Query\GetPostByIdQuery;
use App\Post\Domain\Exception\PostNotFoundException;
use App\Post\Domain\Exception\UnauthorizedPostAccessException;
use App\Post\Domain\Repository\PostRepositoryInterface;
use App\Post\UI\Http\Request\UpdatePostRequest;
use App\Post\UI\Http\Transformer\PostTransformer;
use App\Post\UI\Http\Voter\PostVoter;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class UpdatePostController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly PostRepositoryInterface $postRepository,
        private readonly PostTransformer $postTransformer,
        private readonly Manager $fractal
    ) {
    }

    #[Route('/api/v1/posts/{id}', name: 'posts_update', methods: ['PUT', 'PATCH'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function update(
        string $id,
        #[MapRequestPayload] UpdatePostRequest $request
    ): JsonResponse {
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

            if (!$this->isGranted(PostVoter::UPDATE, $post)) {
                return new JsonResponse(
                    ['error' => 'FORBIDDEN', 'message' => 'You do not have permission to update this post'],
                    Response::HTTP_FORBIDDEN
                );
            }

            $userId = method_exists($user, 'getId') ? $user->getId() : null;

            if ($userId === null) {
                return new JsonResponse(
                    ['error' => 'UNAUTHORIZED', 'message' => 'Invalid user'],
                    Response::HTTP_UNAUTHORIZED
                );
            }

            $command = new UpdatePostCommand(
                postId: $id,
                updatedBy: $userId,
                title: $request->title,
                content: $request->content,
                imageUrls: $request->imageUrls
            );

            $this->messageBus->dispatch($command);

            $query = new GetPostByIdQuery(id: $id);
            $envelope = $this->messageBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);

            if (!$handledStamp instanceof HandledStamp) {
                return new JsonResponse(
                    ['error' => 'UPDATE_FAILED', 'message' => 'Post update failed'],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            $postDTO = $handledStamp->getResult();
            $resource = new Item($postDTO, $this->postTransformer, 'post');
            $transformed = $this->fractal->createData($resource)->toArray();

            return new JsonResponse(
                [
                    'message' => 'Post updated successfully',
                    'post' => $transformed['data'],
                ],
                Response::HTTP_OK
            );
        } catch (HandlerFailedException $e) {
            $originalException = $e->getPrevious();

            if ($originalException instanceof PostNotFoundException) {
                return new JsonResponse(
                    ['error' => 'NOT_FOUND', 'message' => $originalException->getMessage()],
                    Response::HTTP_NOT_FOUND
                );
            }

            if ($originalException instanceof UnauthorizedPostAccessException) {
                return new JsonResponse(
                    ['error' => 'FORBIDDEN', 'message' => $originalException->getMessage()],
                    Response::HTTP_FORBIDDEN
                );
            }

            return new JsonResponse(
                ['error' => 'UPDATE_FAILED', 'message' => 'An error occurred during post update'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'UPDATE_FAILED', 'message' => 'An error occurred during post update'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
