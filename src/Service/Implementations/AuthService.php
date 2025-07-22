<?php

declare(strict_types=1);

namespace App\Service\Implementations;

use App\Dto\UserDtos\AuthenticateUserDto;
use App\Dto\UserDtos\RegisterUserDto;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\IAuthService;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Encoder\JWTEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

readonly class AuthService implements IAuthService
{
    public function __construct(
        private JWTEncoderInterface    $jwtEncoder,
        private UserRepository         $userRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * @throws JWTEncodeFailureException
     */
    public function generateToken(User $user): string
    {
        $username = $user->getUsername();
        $role = $user->getRoles();
        return $this->jwtEncoder->encode([
            'username' => $username,
            'exp' => time() + 1800, // Token valid for 30 minutes
            'role' => $role,
        ]);
    }

    /**
     * @return array{0: string, 1: list<string>|null}|false
     */
    public function getUserFromToken(string $token): array|false
    {
        try {
            $payload = $this->jwtEncoder->decode($token);
            if (!isset($payload['username'])) {
                return false;
            }
            return [
                $payload['username'],
                $payload['role'] ?? null,
            ];

        } catch (Exception) {
            return false; // Token is invalid or expired
        }
    }

    /**
     * @throws AuthenticationException
     * @throws JWTEncodeFailureException
     */
    public function authenticate(AuthenticateUserDto $userDto): string
    {
        $user = $this->userRepository->findOneBy(['username' => $userDto->username]);

        if (!$user) {
            throw new AuthenticationException('User not found');
        }

        if (!password_verify($userDto->password, $user->getPassword())) {
            throw new AuthenticationException('Wrong password');
        }

        return $this->generateToken($user);
    }

    public function register(RegisterUserDto $userDto): void
    {
        // Check if user already exists
        if ($this->userRepository->findOneBy(['username' => $userDto->username])) {
            throw new ConflictHttpException('Username already taken');
        }

        // Create new user entity and persist it
        $user = new User();
        $user->setUsername($userDto->username);
        $user->setPassword(password_hash($userDto->password, PASSWORD_BCRYPT));

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }
}
