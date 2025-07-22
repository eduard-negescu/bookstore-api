<?php

declare(strict_types=1);

namespace App\Service;

interface ICartService
{    
    public function addBookToCart(string $username, int $bookId): void;

    public function removeBookFromCart(string $username, int $bookId): void;

    /**
     * @return list<int>
     */
    public function getCart(string $username): array;

    public function clearCart(string $username): void;

    public function getTotalAmountInCents(string $username): int;
}