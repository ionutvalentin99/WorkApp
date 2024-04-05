<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\EmailVerifier;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(private readonly EmailVerifier $emailVerifier)
    {
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $isVerified = $user?->isVerified();

        return $this->render('home/index.html.twig', [
            'user' => $user,
            'isVerified' => $isVerified
        ]);
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/email/sendVerification', name: 'app_send_email_verification')]
    public function secondVerifyEmail(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
                ->from(new Address('test@pontaje.ro', 'Test'))
                ->to($user->getEmail())
                ->subject('Please Confirm your Email')
                ->htmlTemplate('registration/confirmation_email.html.twig')
        );

        $this->addFlash('success', 'An email confirmation has been sent!');

        return $this->redirectToRoute('app_home');
    }
}
