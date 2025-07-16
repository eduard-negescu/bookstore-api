<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\UserDtos\RegisterUserDto;
use App\Dto\UserDtos\AuthenticateUserDto;
use App\Entity\User;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;

interface IAuthService
{
    public function generateToken(User $user): string;

    /** 
     * @return array{0: string, 1: list<string>|null}|false
     */
    public function getUserFromToken(string $token): array|false;
    
    public function register(RegisterUserDto $userDto): void;

    /**
     * @throws AuthenticationException
     * @throws JWTEncodeFailureException
     */
    public function authenticate(AuthenticateUserDto $userDto): string;
}
