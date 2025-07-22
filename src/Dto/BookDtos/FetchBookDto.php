<?php 

declare(strict_types=1);

namespace App\Dto\BookDtos;

class FetchBookDto extends BaseBookDto
{
    public int $id;
    public int $priceInCents;

    public function __construct(
        int $id,
        string $title,
        ?string $description,
        ?string $cover,
        int $priceInCents
    ) {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->cover = $cover;
        $this->price = $priceInCents / 100;
        $this->priceInCents = $priceInCents;
    }
}
