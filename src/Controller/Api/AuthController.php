<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\IAuthService;
use App\Dto\UserDtos\AuthenticateUserDto;
use App\Dto\UserDtos\RegisterUserDto;
use Exception;
use Nelmio\ApiDocBundle\Attribute\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Auth')]
#[Route('/api/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly IAuthService $authService,
    ) {}

    #[Route('/login', name: 'login', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'User credentials for authentication',
        required: true,
        content: new OA\JsonContent(
            ref: new Model(type: AuthenticateUserDto::class)
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Authentication successful',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string'),
                new OA\Property(property: 'token', type: 'string'),
            ],
            type: 'object'
        )
    )]
    public function login(#[MapRequestPayload] AuthenticateUserDto $authenticateUserDto): Response {
        try {
            $token = $this->authService->authenticate($authenticateUserDto);
            return $this->json([
                'message' => 'Authentication successful',
                'token' => $token,
            ], Response::HTTP_OK);
        } catch (AuthenticationException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_UNAUTHORIZED);
        }
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    #[OA\RequestBody(
        description: 'User data for registration',
        required: true,
        content: new OA\JsonContent(
            ref: new Model(type: RegisterUserDto::class)
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Registration successful',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string'),
            ],
            type: 'object'
        )
    )]
    public function register(#[MapRequestPayload] RegisterUserDto $registerUserDto): Response {
       try {
            $this->authService->register($registerUserDto);
            return $this->json([
                'message' => 'Registration successful',
            ], Response::HTTP_CREATED);
        } catch (Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_CONFLICT);
        }
    }

    #[Route('/user', name: 'get_user', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(
        description: 'Returns user data for the authenticated user',
        summary: 'Get user data from token',
        security: [['bearerAuth' => []]]
    )]
    #[OA\Response(
        response: 200,
        description: 'User data retrieved successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'username', type: 'string'),
                new OA\Property(property: 'roles', type: 'array', items: new OA\Items(type: 'string'))
            ]
        )
    )]
    public function getUserFromToken(): Response
    {
        // Get the authenticated user
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'message' => 'User not found'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json([
            'username' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
        ]);
    }
}
