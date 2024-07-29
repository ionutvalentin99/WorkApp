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
use Symfony\Component\Routing\Annotation\Route;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    /**
     * @throws ApiErrorException
     */
    #[Route('/checkout', name: 'app_stripe_checkout')]
    public function checkout(StripeService $stripeService): Response
    {
        $checkout_session = $stripeService->checkout();

        return $this->redirect($checkout_session->url, 303);
    }

    #[Route('/success', name: 'app_stripe_success')]
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

    #[Route('/failed', name: 'app_stripe_failed')]
    public function failed(CompanyRepository $repository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $company = $user->getCompany();
        if (!$company || $company->isPaid()) {
            return $this->redirectToRoute('app_home');
        }
        $user->setCompany(null);
        $repository->remove($company, true);

        return $this->render('payment/failed.html.twig');
    }
}
