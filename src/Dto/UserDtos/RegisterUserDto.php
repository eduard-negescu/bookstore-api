<?php 

declare(strict_types=1);

namespace App\Dto\UserDtos;

use Symfony\Component\Validator\Constraints as Assert;

class RegisterUserDto extends BaseUserDto
{
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 20)]
    public string $username;

    #[Assert\NotBlank]
    #[Assert\Length(min: 6, max: 4096)]
    public string $password;
}