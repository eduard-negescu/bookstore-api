<?php

declare(strict_types=1);

namespace App\Service;

interface IStripeService
{
    public function checkoutSession(int $amountInCents, string $successUrl, string $cancelUrl): string;
}