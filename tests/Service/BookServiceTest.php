<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\Implementations\BookService;
use App\Repository\BookRepository;
use App\Entity\Book;
use App\Dto\BookDtos\FetchBookDto;
use App\Dto\BookDtos\SaveBookDto;
use App\Dto\BookDtos\UpdateBookDto;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class BookServiceTest extends TestCase
{
    private BookRepository $bookRepository;

    /** @var \PHPUnit\Framework\MockObject\MockObject&\Doctrine\ORM\EntityManagerInterface */
    private EntityManagerInterface $entityManager;
    
    private BookService $bookService;

    private function setEntityId(object $entity, int $id): void
    {
        $ref = new \ReflectionClass($entity);
        $prop = $ref->getProperty('id');
        $prop->setAccessible(true);
        $prop->setValue($entity, $id);
    }

    protected function setUp(): void
    {
        $this->bookRepository = $this->getMockBuilder(BookRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->entityManager = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->bookService = new BookService($this->bookRepository, $this->entityManager);
    }

    public function testFetchBooksReturnsDtos(): void
    {
        $book = (new Book())
            ->setTitle('Test Book')
            ->setDescription('Desc')
            ->setCover('cover.jpg')
            ->setPriceInCents(1000);

        $this->setEntityId($book, 1);

        $this->bookRepository->method('findAll')->willReturn([$book]);

        $result = $this->bookService->fetchBooks();

        $this->assertCount(1, $result);
        $this->assertInstanceOf(FetchBookDto::class, $result[0]);
        $this->assertSame('Test Book', $result[0]->title);
    }

    public function testFetchBooksThrowsWhenEmpty(): void
    {
        $this->bookRepository->method('findAll')->willReturn([]);

        $this->expectException(InvalidArgumentException::class);
        $this->bookService->fetchBooks();
    }

    public function testSaveBookPersistsAndFlushes(): void
    {
        $dto = new SaveBookDto(
            title: 'New Book',
            description: 'A book',
            cover: 'cover.jpg',
            price: 12.99
        );

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $this->bookService->saveBook($dto);
    }

    public function testUpdateBookThrowsIfNotFound(): void
    {
        $this->bookRepository->method('find')->willReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->bookService->updateBook(1, new UpdateBookDto(
            title: 'Updated',
            description: 'desc',
            cover: 'cover.jpg',
            price: 10.00
        ));
    }

    public function testUpdateBookUpdatesAndFlushes(): void
    {
        $book = (new Book())
            ->setTitle('Old Title')
            ->setDescription('Old')
            ->setCover('old.jpg')
            ->setPriceInCents(1000);

        $this->bookRepository->method('find')->willReturn($book);

        $this->entityManager->expects($this->once())->method('flush');

        $dto = new UpdateBookDto(
            title: 'New Title',
            description: 'Old',
            cover: 'old.jpg',
            price: 10.00
        );

        $this->bookService->updateBook(1, $dto);

        $this->assertSame('New Title', $book->getTitle());
    }

    public function testDeleteBookRemovesEntity(): void
    {
        $book = new Book();
        $this->bookRepository->method('find')->willReturn($book);

        $this->entityManager->expects($this->once())->method('remove')->with($book);
        $this->entityManager->expects($this->once())->method('flush');

        $this->bookService->deleteBook(1);
    }

    public function testDeleteBookThrowsIfNotFound(): void
    {
        $this->bookRepository->method('find')->willReturn(null);
        $this->expectException(InvalidArgumentException::class);
        $this->bookService->deleteBook(999);
    }

    public function testFetchBookByIdReturnsDto(): void
    {
        $book = (new Book())
            ->setTitle('Book')
            ->setDescription('desc')
            ->setCover('cover.jpg')
            ->setPriceInCents(1200);

        $this->setEntityId($book, 1);

        $this->bookRepository->method('find')->willReturn($book);

        $dto = $this->bookService->fetchBookById(1);

        $this->assertInstanceOf(FetchBookDto::class, $dto);
        $this->assertSame('Book', $dto->title);
    }

    public function testFetchBookByIdThrowsIfNotFound(): void
    {
        $this->bookRepository->method('find')->willReturn(null);
        $this->expectException(InvalidArgumentException::class);
        $this->bookService->fetchBookById(123);
    }
}
