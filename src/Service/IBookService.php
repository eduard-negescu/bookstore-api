<?php 

declare(strict_types=1);

namespace App\Service;

use App\Dto\BookDtos\SaveBookDto;
use App\Dto\BookDtos\FetchBookDto;
use App\Dto\BookDtos\UpdateBookDto;

interface IBookService
{
    /** @return list<FetchBookDto> */
    public function fetchBooks(): array;

    public function saveBook(SaveBookDto $saveBookDto): void;

    public function updateBook(int $id, UpdateBookDto $updateBookDto): void;

    public function fetchBookById(int $id): FetchBookDto|null;
    
    public function deleteBook(int $id): void;
}