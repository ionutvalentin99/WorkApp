<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\EmailVerificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(SessionInterface $session): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $isVerified = $user?->isVerified();

        if (!$session->has('darkMode')) {
            $session->set('darkMode', true);
        }

        return $this->render('home/index.html.twig', [
            'user' => $user,
            'isVerified' => $isVerified
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/email/sendVerification', name: 'app_send_email_verification')]
    public function secondVerifyEmail(EmailVerificationService $emailVerification): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $emailVerification->sendEmail($user);
        $this->addFlash('success', 'An email confirmation has been sent!');

        return $this->redirectToRoute('app_home');
    }
}
