<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PontajeRepository;
use App\Repository\UserRepository;
use App\Service\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/account', name: 'app_account_settings')]
    public function account(): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('user/account.html.twig', [
            'userEmail' => $user->getEmail(),
        ]);
    }

    #[Route('/profile', name: 'app_profile', methods: ['GET'])]
    public function profile(): Response
    {
        return $this->render('user/profile.html.twig');
    }

    /**
     * @throws TransportExceptionInterface
     */
    #[Route('/change-email', name: 'app_change_email')]
    public function changeEmail(Request $request, EntityManagerInterface $entityManager, EmailVerificationService $emailVerificationService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($request->getMethod() === 'POST') {
            $newEmail = $request->request->get('new-email');
            $confirmEmail = $request->request->get('confirm-email');
            if ($newEmail !== $confirmEmail) {
                throw new BadRequestHttpException('New email does not match confirm email');
            }
            $user->setEmail($newEmail);
            $user->setIsVerified(0);
            $entityManager->flush();

            $emailVerificationService->sendEmail($user);

            return $this->redirectToRoute('app_account_settings');
        }

        return $this->render('user/changeEmail.html.twig');
    }

    #[Route('/change-password', name: 'app_change_password')]
    public function changePassword(
        Request                     $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface      $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentPassword = $request->request->get('current-password');
        $newPassword = $request->request->get('new-password');
        if ($newPassword) {
            if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                throw new BadRequestHttpException('Wrong current password.');
            }
        } else {
            return $this->redirectToRoute('app_account_settings');
        }

        $encodedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($encodedPassword);
        $entityManager->flush();

        return $this->redirectToRoute('app_account_settings');
    }

    #[Route('/change-name', name: 'app_change_name')]
    public function changeName(
        Request                $request,
        EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($request->getMethod() === 'POST') {
            $firstName = $request->request->get('first-name');
            $lastName = $request->request->get('last-name');
            if (!$firstName || !$lastName) {
                throw new BadRequestHttpException('First name and last name are required.');
            }
            $user->setFirstName($firstName);
            $user->setLastName($lastName);

            $entityManager->flush();
        }

        return $this->redirectToRoute('app_account_settings');
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Route('/delete-account', name: 'app_delete_account', methods: ['POST'])]
    public function deleteAccount(Request $request, EntityManagerInterface $em, PontajeRepository $repository): Response
    {
        if ($this->isCsrfTokenValid('delete-user', $request->request->get('_token'))) {
            /** @var User $user */
            $user = $this->getUser();
            $company = $user->getCompany() ?? null;
            $userWorkRecords = $repository->findBy(['user' => $user]);
            if ($company->getOwner() === $user) {
                return $this->redirectToRoute('app_account_settings', ['error' => 'You cannot delete your account, you are the owner of a company.']);
            }
            foreach ($userWorkRecords as $pontaje) {
                $em->remove($pontaje);
            }

            $em->remove($user);
            $em->flush();

            $this->container->get('security.token_storage')->setToken();
            $request->getSession()->invalidate();

            return $this->redirectToRoute('app_home');
        }

        return $this->redirectToRoute('app_account_settings', ['error' => 'Invalid CSRF token']);
    }
}