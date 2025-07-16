<?php 

declare(strict_types=1);

namespace App\Dto\UserDtos;

use Symfony\Component\Validator\Constraints as Assert;

class AuthenticateUserDto extends BaseUserDto
{
    #[Assert\NotBlank]
    public string $username;

    #[Assert\NotBlank]
    public string $password;
}