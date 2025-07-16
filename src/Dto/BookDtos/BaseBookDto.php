<?php 

declare(strict_types=1);

namespace App\Dto\BookDtos;

class BaseBookDto
{
    public string $title;
    public ?string $description;
    public ?string $cover;
    public float $price;
}