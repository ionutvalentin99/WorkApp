<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CompanyRepository;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PaymentController extends AbstractController
{
    public function __construct(private readonly \App\Service\StripeService $stripeService, private readonly \App\Repository\CompanyRepository $repository)
    {
    }
    /**
     * @throws ApiErrorException
     */
    #[Route('/payment/checkout', name: 'app_stripe_checkout')]
    public function checkout(): Response
    {
        $checkout_session = $this->stripeService->checkout();
        return $this->redirect($checkout_session->url, Response::HTTP_SEE_OTHER);
    }
    #[Route('/payment/success', name: 'app_stripe_success')]
    public function success(EntityManagerInterface $manager, Request $request): Response
    {
        //TODO: on future implement stripe webhook
        /** @var User $user */
        $user = $this->getUser();
        $company = $user->getCompany();
        $sessionId = $request->query->get('session_id');
        $companyId = $company?->getId();
        if ($sessionId && ($request->query->get('company_id') == $companyId)) {
            $company->setIsPaid(true);
            $manager->flush();

            return $this->render('payment/success.html.twig');
        }

        return $this->redirectToRoute('app_stripe_failed');
    }
    #[Route('/payment/failed', name: 'app_stripe_failed')]
    public function failed(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $company = $user->getCompany();
        if (!$company || $company->isPaid()) {
            return $this->redirectToRoute('app_home');
        }
        $user->setCompany(null);
        $this->repository->remove($company, true);
        return $this->render('payment/failed.html.twig');
    }
}
