<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Service\IStripeService;
use App\Service\ICartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

#[Route('/api/payment')]
#[OA\Tag(name: 'Payment')]
class PaymentController extends AbstractController
{
    public function __construct(
        private readonly IStripeService $stripeService,
        private readonly ICartService $cartService,
    ) {}

    #[Route('/checkout', name: 'api_checkout_session', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[OA\Post(
        summary: 'Create a checkout session for payment',
        security: [['bearerAuth' => []]],
    )]
    #[OA\Response(
        response: 200,
        description: 'Returns the checkout session URL',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'url', type: 'string'),
            ]
        )
    )]
    public function createCheckoutSession(): Response
    {
        $amountInCents = $this->cartService->getTotalAmountInCents($this->getUser()->getUserIdentifier());

        $successUrl = $this->generateUrl('api_payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL);
        $cancelUrl = $this->generateUrl('api_payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL);
        
        $sessionUrl = $this->stripeService->checkoutSession($amountInCents, $successUrl, $cancelUrl);
        
        return $this->json(['url' => $sessionUrl], Response::HTTP_OK);
    }

    #[Route('/success', name: 'api_payment_success', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Payment was successful',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Payment successful!'),
            ]
        )
    )]
    public function paymentSuccess(): Response
    {
        return $this->json(['message' => 'Payment successful!'], Response::HTTP_OK);
    }

    #[Route('/cancel', name: 'api_payment_cancel', methods: ['GET'])]
    #[OA\Response(
        response: 200,
        description: 'Payment was cancelled',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'message', type: 'string', example: 'Payment cancelled.'),
            ]
        )
    )]
    public function paymentCancel(): Response
    {
        return $this->json(['message' => 'Payment cancelled.'], Response::HTTP_OK);
    }
}