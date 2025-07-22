<?php

declare(strict_types=1);

namespace App\Service\Implementations;

use App\Service\IBookService;
use App\Dto\BookDtos\FetchBookDto;
use App\Dto\BookDtos\SaveBookDto;
use App\Dto\BookDtos\UpdateBookDto;
use App\Repository\BookRepository;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;

readonly class BookService implements IBookService
{
    public function __construct(
        private BookRepository         $bookRepository,
        private EntityManagerInterface $entityManager
    ) {}

    /** 
     * @return list<FetchBookDto> 
     */
    public function fetchBooks(): array
    {
        $books_db = $this->bookRepository->findAll();

        $bookDtos = array_map(function (Book $book) {
            return new FetchBookDto(
                id: $book->getId(),
                title: $book->getTitle(),
                description: $book->getDescription(),
                cover: $book->getCover(),
                priceInCents: $book->getPriceInCents()
            );
        }, $books_db);

        if (empty($bookDtos)) {
            throw new InvalidArgumentException("No books found.");
        }

        return $bookDtos;
    }

    public function saveBook(SaveBookDto $saveBookDto): void
    {
        $book = new Book();
        $book->setTitle($saveBookDto->title)
            ->setDescription($saveBookDto->description)
            ->setCover($saveBookDto->cover)
            ->setPriceInCents((int) round($saveBookDto->price * 100));

        $this->entityManager->persist($book);
        $this->entityManager->flush();
    }

    public function updateBook(int $id, UpdateBookDto $updateBookDto): void
    {
        $book = $this->bookRepository->find($id);

        if (!$book) {
            throw new InvalidArgumentException("Book with ID $id not found.");
        }

        if ($updateBookDto->title !== null) {
            $book->setTitle($updateBookDto->title);
        }
        if ($updateBookDto->description !== null) {
            $book->setDescription($updateBookDto->description);
        }
        if ($updateBookDto->cover !== null) {
            $book->setCover($updateBookDto->cover);
        }
        if ($updateBookDto->price !== null) {
            $book->setPriceInCents((int) round($updateBookDto->price * 100));
        }

        $this->entityManager->persist($book);
        $this->entityManager->flush();
    }

    public function fetchBookById(int $id): ?FetchBookDto
    {
        $book = $this->bookRepository->find($id);

        if (!$book) {
            throw new InvalidArgumentException("Book with ID $id not found.");
        }

        return new FetchBookDto(
            id: $book->getId(),
            title: $book->getTitle(),
            description: $book->getDescription(),
            cover: $book->getCover(),
            priceInCents: $book->getPriceInCents()
        );
    }


    public function deleteBook(int $id): void
    {
        $book = $this->bookRepository->find($id);

        if (!$book) {
           throw new InvalidArgumentException("Book with ID $id not found.");
        }

        $this->entityManager->remove($book);
        $this->entityManager->flush();
    }
}
