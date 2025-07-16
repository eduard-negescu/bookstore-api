<?php

declare(strict_types=1);

namespace App\Service\Implementations;

use App\Service\ICartService;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class CartService implements ICartService
{
    private const EXPIRE_TIME = 3600; // 1 hour
    private const CACHE_NAMESPACE = 'cart_user_';
    
    public function __construct(
        private CacheInterface $cache,
    ) 
    {
    }
    
    private function getCacheKey(string $username): string
    {
        return self::CACHE_NAMESPACE . $username;
    }
    
    public function addBookToCart(string $username, int $bookId): void
    {
        $key = $this->getCacheKey($username);
        $basket = $this->getCart($username);

        if (!in_array($bookId, $basket)) {
            $basket[] = $bookId;
        }

        $this->cache->delete($key);

        $this->cache->get($key, function (ItemInterface $item) use ($basket) {
            $item->expiresAfter(self::EXPIRE_TIME);
            return $basket;
        });

    }

    public function removeBookFromCart(string $username, int $bookId): void
    {
        $key = $this->getCacheKey($username);
        $basket = $this->getCart($username);

        $new_basket = array_values(array_filter($basket, fn($id) => $id !== $bookId));
        
        $this->cache->delete($key);

        $this->cache->get($key, function (ItemInterface $item) use ($new_basket) {
            $item->expiresAfter(self::EXPIRE_TIME);
            return $new_basket;
        });
    }

    /**
     * @return list<int>
     */
    public function getCart(string $username): array
    {
        $cacheKey = $this->getCacheKey($username);
        
        try {
            return $this->cache->get($cacheKey, function (ItemInterface $item) {
                $item->expiresAfter(self::EXPIRE_TIME);
                return [];
            });
        } catch (\Exception $e) {
            // Handle cache retrieval failure, return an empty cart
            return [];
        }
    }

    public function clearCart(string $username): void
    {
        $key = $this->getCacheKey($username);
        $this->cache->delete($key);
    }
}