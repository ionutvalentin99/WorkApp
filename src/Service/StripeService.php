<?php

declare(strict_types=1);

namespace App\Service;

use Stripe\Checkout\Session;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class StripeService
{
    private ?StripeClient $stripeClient = null;

    public function __construct(
        private readonly ParameterBagInterface $params,
        private readonly RouterInterface       $router,
        private readonly Security              $security,
    )
    {
        $this->setStripeClient();
    }

    public function setStripeClient(): void
    {
        if (null === $this->stripeClient) {
            $this->stripeClient = new StripeClient($this->params->get('stripe_key'));
        }
    }

    /**
     * @throws ApiErrorException
     */
    public function checkout(): Session
    {
        $companyId = $this->security->getUser()?->getCompany()?->getId();
        return $this->stripeClient->checkout->sessions->create([
            'line_items' => [[
                'price_data' => [
                    'currency' => 'ron',
                    'product_data' => [
                        'name' => 'Company',
                    ],
                    'unit_amount' => 10000,
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => $this->router->generate('app_stripe_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}&company_id=' . $companyId,
            'cancel_url' => $this->router->generate('app_stripe_failed', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }
}