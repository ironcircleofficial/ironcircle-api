<?php

declare(strict_types=1);

namespace App\Auth\UI\Http\Controller;

use App\Auth\Application\DTO\TokenDTO;
use App\Auth\Application\Query\LoginUserQuery;
use App\Auth\Domain\Exception\InvalidCredentialsException;
use App\Auth\UI\Http\Request\LoginUserRequest;
use App\Auth\UI\Http\Request\RegisterUserRequest;
use App\User\Application\Command\RegisterUserCommand;
use App\User\Domain\Exception\UserAlreadyExistsException;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\UI\Http\Transformer\UserTransformer;
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

#[Route('/api/v1/auth', name: 'auth_')]
final class AuthController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $messageBus,
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserTransformer $userTransformer,
        private readonly Manager $fractal
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(
        #[MapRequestPayload] RegisterUserRequest $request
    ): JsonResponse {
        try {
            $command = new RegisterUserCommand(
                username: $request->username,
                email: $request->email,
                password: $request->password
            );

            $this->messageBus->dispatch($command);

            $user = $this->userRepository->findByUsername($request->username);

            if ($user === null) {
                return new JsonResponse(
                    ['error' => 'REGISTRATION_FAILED', 'message' => 'User registration failed'],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            $resource = new Item($user, $this->userTransformer, 'user');
            $transformedUser = $this->fractal->createData($resource)->toArray();

            return new JsonResponse(
                [
                    'message' => 'User registered successfully',
                    'user' => $transformedUser['data']
                ],
                Response::HTTP_CREATED
            );
        } catch (HandlerFailedException $e) {
            $originalException = $e->getPrevious();
            
            if ($originalException instanceof UserAlreadyExistsException) {
                return new JsonResponse(
                    ['error' => 'USER_ALREADY_EXISTS', 'message' => $originalException->getMessage()],
                    Response::HTTP_CONFLICT
                );
            }
            
            return new JsonResponse(
                ['error' => 'REGISTRATION_FAILED', 'message' => 'An error occurred during registration'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (UserAlreadyExistsException $e) {
            return new JsonResponse(
                ['error' => 'USER_ALREADY_EXISTS', 'message' => $e->getMessage()],
                Response::HTTP_CONFLICT
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'REGISTRATION_FAILED', 'message' => 'An error occurred during registration'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(
        #[MapRequestPayload] LoginUserRequest $request
    ): JsonResponse {
        try {
            $query = new LoginUserQuery(
                username: $request->username,
                password: $request->password
            );

            $envelope = $this->messageBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);

            if (!$handledStamp instanceof HandledStamp) {
                return new JsonResponse(
                    ['error' => 'LOGIN_FAILED', 'message' => 'Login failed'],
                    Response::HTTP_INTERNAL_SERVER_ERROR
                );
            }

            /** @var TokenDTO $tokenDTO */
            $tokenDTO = $handledStamp->getResult();

            return new JsonResponse(
                [
                    'message' => 'Login successful',
                    'token' => $tokenDTO->token,
                    'user' => [
                        'id' => $tokenDTO->userId,
                        'username' => $tokenDTO->username,
                    ]
                ],
                Response::HTTP_OK
            );
        } catch (HandlerFailedException $e) {
            $originalException = $e->getPrevious();
            
            if ($originalException instanceof InvalidCredentialsException) {
                return new JsonResponse(
                    ['error' => 'INVALID_CREDENTIALS', 'message' => $originalException->getMessage()],
                    Response::HTTP_UNAUTHORIZED
                );
            }
            
            return new JsonResponse(
                ['error' => 'LOGIN_FAILED', 'message' => 'An error occurred during login'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        } catch (InvalidCredentialsException $e) {
            return new JsonResponse(
                ['error' => 'INVALID_CREDENTIALS', 'message' => $e->getMessage()],
                Response::HTTP_UNAUTHORIZED
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'LOGIN_FAILED', 'message' => 'An error occurred during login'],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        return new JsonResponse(
            ['message' => 'Logout successful. Please remove the token from client storage.'],
            Response::HTTP_OK
        );
    }
}
