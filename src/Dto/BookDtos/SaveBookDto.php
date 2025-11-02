<?php 

declare(strict_types=1);

namespace App\Dto\BookDtos;

use Symfony\Component\Validator\Constraints as Assert;

class SaveBookDto extends BaseBookDto
{
    #[Assert\NotBlank(
        message: 'Title cannot be blank.'
    )]
    #[Assert\Length(
        min: 3, 
        max: 35, 
        minMessage: 'Title must be at least 3 characters.',
        maxMessage: 'Title cannot be longer than 20 characters.'
    )]
    public string $title;
    
    #[Assert\Length(
        max: 1000, 
        maxMessage: 'Description must not exceed 1000 characters.'
    )]
    public ?string $description;
    
    #[Assert\Url(
        message: 'Cover address must be a valid URL.'
    )]
    public ?string $cover;

    #[Assert\NotBlank(
        message: 'Price cannot be blank.'
    )]
    #[Assert\GreaterThan(
        value: 0,
        message: 'Price must be greater than 0.'
    )]
    #[Assert\Regex(
        pattern: '/^\d+(\.\d{1,2})?$/',
        message: 'Price must have at most two digits after the decimal point.'
    )]
    public float $price;

    public function __construct(
        string $title,
        ?string $description,
        ?string $cover,
        float $price
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->cover = $cover;
        $this->price = $price;
    }
}