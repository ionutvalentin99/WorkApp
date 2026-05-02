<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CompanyRepository;
use App\Service\ActiveCompanyService;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Exception\ApiErrorException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PaymentController extends AbstractController
{
    public function __construct(
        private readonly StripeService $stripeService,
        private readonly CompanyRepository $repository,
        private readonly ActiveCompanyService $activeCompanyService,
    ) {}

    /**
     * @throws ApiErrorException
     */
    #[Route('/payment/checkout', name: 'app_stripe_checkout')]
    public function checkout(): Response
    {
        $company = $this->activeCompanyService->getActiveCompany();
        if (!$company) {
             return $this->redirectToRoute('app_home');
        }
        $checkout_session = $this->stripeService->checkout($company->getId());
        return $this->redirect($checkout_session->url, Response::HTTP_SEE_OTHER);
    }
    #[Route('/payment/success', name: 'app_stripe_success')]
    public function success(EntityManagerInterface $manager, Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            return $this->redirectToRoute('app_home');
        }

        try {
            $session = $this->stripeService->retrieveSession($sessionId);
        } catch (ApiErrorException) {
            return $this->redirectToRoute('app_home');
        }

        if ($session->payment_status !== 'paid' || $session->status !== 'complete') {
            return $this->redirectToRoute('app_stripe_failed', ['session_id' => $sessionId]);
        }

        $companyId = $session->metadata['company_id'] ?? null;
        $company = $companyId ? $this->repository->find($companyId) : null;

        if (!$company || $company->getOwner() !== $user) {
            return $this->redirectToRoute('app_home');
        }

        if (!$company->isPaid()) {
            $company->setIsPaid(true);
            $manager->flush();
        }

        return $this->render('payment/success.html.twig');
    }

    #[Route('/payment/failed', name: 'app_stripe_failed')]
    public function failed(Request $request): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $sessionId = $request->query->get('session_id');
        $company = null;

        if ($sessionId) {
            try {
                $session = $this->stripeService->retrieveSession($sessionId);
                $companyId = $session->metadata['company_id'] ?? null;
                $company = $companyId ? $this->repository->find($companyId) : null;
            } catch (ApiErrorException) {
                // session invalidă sau expirată
            }
        }

        if (!$company || $company->getOwner() !== $user || $company->isPaid()) {
            return $this->redirectToRoute('app_home');
        }

        $this->activeCompanyService->setActiveCompany($company);

        return $this->render('payment/failed.html.twig', [
            'company' => $company,
        ]);
    }
}
