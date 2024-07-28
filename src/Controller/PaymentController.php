<?php

namespace App\Controller;

use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    #[Route('', name: 'app_stripe_payment')]
    public function index(): Response
    {
        return $this->render('payment/index.html.twig');
    }

    /**
     * @throws ApiErrorException
     */
    #[Route('/checkout', name: 'app_stripe_checkout')]
    public function checkout($stripeSK): Response
    {
        $stripe = new StripeClient($stripeSK);
        $checkout_session = $stripe->checkout->sessions->create([
            'line_items' => [[
                'price_data' => [
                    'currency' => 'ron',
                    'product_data' => [
                        'name' => 'Company',
                    ],
                    'unit_amount' => 2500,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('app_stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('app_stripe_failed', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($checkout_session->url, 303);
    }

    #[Route('/success', name: 'app_stripe_success')]
    public function success(): Response
    {
        return $this->render('payment/success.html.twig');
    }

    #[Route('/failed', name: 'app_stripe_failed')]
    public function failed(): Response
    {
        return $this->render('payment/failed.html.twig');
    }
}
