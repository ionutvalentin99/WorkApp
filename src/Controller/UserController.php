<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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

    #[Route('/change-password', name: 'app_change_password')]
    public function changePassword(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $currentPassword = $request->request->get('current-password');
        $newPassword = $request->request->get('new-password');
        if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
            throw new BadRequestHttpException('Wrong current password.');
        }

        $encodedPassword = $passwordHasher->hashPassword($user, $newPassword);
        $user->setPassword($encodedPassword);
        $entityManager->flush();

        return $this->redirectToRoute('app_account_settings');
    }
}
