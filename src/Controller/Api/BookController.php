<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Dto\BookDtos\FetchBookDto;
use App\Dto\BookDtos\SaveBookDto;
use App\Dto\BookDtos\UpdateBookDto;
use App\Service\IBookService;
use Exception;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;

#[Route('/api/book')]
#[OA\Tag(name: 'Books')]
final class BookController extends AbstractController
{
    public function __construct(
        private readonly IBookService $bookService,
    ) 
    {
    }

    #[Route(name: 'api_get_book_list', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Returns a list of books',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: FetchBookDto::class))
        )
    )]
    public function index(): Response
    {
        try {
            $books = $this->bookService->fetchBooks();
            return $this->json([
                'books' => $books,
            ]);
        } catch (InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    #[Route(name: 'api_create_book', methods: ['POST'])]
    #[IsGranted('ROLE_ADMIN', message: 'You do not have permission to create a book.')]
    #[OA\Post(
        summary: 'Create a new book, requires admin role',
        security: [['bearerAuth' => []]],
    )]
    #[OA\RequestBody(
        description: 'Book data to create',
        required: true,
        content: new OA\JsonContent(
            ref: new Model(type: SaveBookDto::class)
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Book created successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Book created successfully')
            ],
            type: 'object'
        )
    )]
    public function new(#[MapRequestPayload('json')] SaveBookDto $saveBookDto): Response
    {
        try {
            $this->bookService->saveBook($saveBookDto);
        } catch (Exception $e) {
            return $this->json(['message' => 'Error saving book: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(
            ['message' => 'Book created successfully'],
            Response::HTTP_CREATED,
        );
    }

    #[Route('/{id}', name: 'api_update_book', methods: ['PUT'])]
    #[IsGranted('ROLE_ADMIN', message: 'You do not have permission to update a book.')]
    #[OA\Put(
        summary: 'Update a book by ID, requires admin role',
        security: [['bearerAuth' => []]],
    )]
    #[OA\RequestBody(
        description: 'Book data to update',
        required: true,
        content: new OA\JsonContent(
            ref: new Model(type: UpdateBookDto::class)
        )
    )]
    #[OA\Response(
        response: 200,
        description: 'Book updated successfully',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Succesfully updated book with id: 1')
            ],
            type: 'object'
        )
    )]
    public function update(int $id, #[MapRequestPayload] UpdateBookDto $updateBookDto): Response
    {
        try {
            $this->bookService->updateBook($id, $updateBookDto);
        } catch (InvalidArgumentException $e) {
            return $this->json(['message' => 'Error updating book: ' . $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return $this->json(['message' => "Succesfully updated book with id: $id"]);
    }

    #[Route('/{id}', name: 'api_get_book', methods: ['GET'])]
    #[OA\Parameter(name: 'id', description: 'ID of the book', in: 'path', required: true)]
    #[OA\Response(
        response: 200,
        description: 'Returns a book by ID',
        content: new OA\JsonContent(
            ref: new Model(type: FetchBookDto::class)
        )
    )]
    public function show(int $id): Response
    {
        try {
            $book = $this->bookService->fetchBookById($id);
        } catch (InvalidArgumentException $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'book' => $book,
        ]);
    }

    #[Route('/{id}', name: 'api_delete_book', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN', message: 'You do not have permission to delete a book.')]
    #[OA\Delete(
        summary: 'Delete a book by ID, requires admin role',
        security: [['bearerAuth' => []]],
    )]
    #[OA\Parameter(name: 'id', description: 'ID of the book', in: 'path', required: true)]
    #[OA\Response(
        response: 204,
        description: 'Book deleted successfully'
    )]
    public function delete(int $id): Response
    {
        try {
            $this->bookService->deleteBook($id);
        } catch (Exception $e) {
            return $this->json(['message' => 'Error deleting book: ' . $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['message' => "Succesfully deleted book with id: $id"], Response::HTTP_NO_CONTENT);
    }
}
