<?php

declare(strict_types=1);

namespace App\Service\Implementations;

use App\Service\IStripeService;
use Stripe\Checkout\Session;
use Stripe\Stripe;

readonly class StripeService implements IStripeService
{
    public function checkoutSession(int $amountInCents, string $successUrl, string $cancelUrl): string
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'ron',
                    'product_data' => [
                        'name' => 'Bookstore Purchase',
                    ],
                    'unit_amount' => $amountInCents, // Amount in cents
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
        ]);

        return $session->url;
    }
}
