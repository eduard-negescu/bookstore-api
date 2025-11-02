<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\IBookService;
use App\Service\ICartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Attributes as OA;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/cart')]
#[OA\Tag(name: 'Cart')]
class CartController extends AbstractController
{
    public function __construct(
        private readonly ICartService $cartService,
        private readonly IBookService $bookService,
    ) {}

    #[Route('/{bookId}', name: 'api_add_book_to_cart', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(
        summary: 'Add a book to the user\'s cart',
        security: [['bearerAuth' => []]],
    )]
    #[OA\Response(
        response: 200,
        description: 'Adds a book to the user\'s cart',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    public function addBookToCart(int $bookId): Response
    {
        $username = $this->getUser()->getUserIdentifier();

        try {
            $this->bookService->fetchBookById($bookId);
        } catch (\Exception $e) {
            return $this->json(['message' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        $this->cartService->addBookToCart($username, $bookId);
        return $this->json(['message' => 'Book added to cart'], Response::HTTP_OK);
    }

    #[Route('/{bookId}', name: 'api_remove_book_from_cart', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Delete(
        summary: 'Remove a book from the user\'s cart',
        security: [['bearerAuth' => []]],
    )]
    #[OA\Response(
        response: 200,
        description: 'Removes a book from the user\'s cart',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    public function removeBookFromCart(int $bookId): Response
    {
        $username = $this->getUser()->getUserIdentifier();
        $this->cartService->removeBookFromCart($username, $bookId);
        return $this->json(['message' => 'Book removed from cart'], Response::HTTP_OK);
    }


    #[Route(name: 'api_get_cart', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(
        summary: 'Get the user\'s cart',
        security: [['bearerAuth' => []]],
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the user\'s cart',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(type: 'integer')
        )
    )]
    public function getCart(): Response
    {
        $username = $this->getUser()->getUserIdentifier();
        $cart = $this->cartService->getCart($username);
        return $this->json($cart, Response::HTTP_OK);
    }

    #[Route(name: 'api_clear_cart', methods: ['DELETE'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Delete(
        summary: 'Clear the user\'s cart',
        security: [['bearerAuth' => []]],
    )]
    #[OA\Response(
        response: 200,
        description: 'Clears the user\'s cart',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'message', type: 'string')
            ]
        )
    )]
    public function clearCart(): Response
    {
        $username = $this->getUser()->getUserIdentifier();
        $this->cartService->clearCart($username);
        return $this->json(['message' => 'Cart cleared'], Response::HTTP_OK);
    }

    #[Route('/total', name: 'api_get_cart_total', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Get(
        summary: 'Get the total amount from the user\'s cart',
        security: [['bearerAuth' => []]],
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the total amount from the user\'s cart',
        content: new OA\JsonContent(
            type: 'object',
            properties: [
                new OA\Property(property: 'total', type: 'integer')
            ]
        )
    )]
    public function getTotalAmountFromCart(): Response
    {
        $username = $this->getUser()->getUserIdentifier();
        $total = $this->cartService->getTotalAmountInCents($username);
        return $this->json(['total' => (float)$total / 100], Response::HTTP_OK);
    }
}
