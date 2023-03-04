<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterFormType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_registration')]
    public function register(Request $request, UserRepository $users, UserPasswordHasherInterface $passwordHasher): Response
    {
        $form = $this->createForm(RegisterFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            $user = new User();
            $user->setUsername($data['username']);
            $user->setEmail($data['email']);
            $user->setFirstName($data['firstname']);
            $user->setLastName($data['lastname']);
            $user->setCreated(new DateTime());
            $plainPassword = ($data['password']);
            $data['password'] = $passwordHasher->hashPassword($user, $plainPassword);

            $user->setPassword($data['password']);
            $users->add($user, true);

            $this->addFlash('success', 'You are now registered!');

            return $this->redirectToRoute('app_login');
        }
        return $this->render(
            'registration/index.html.twig',
            [
                'form' => $form
            ]
        );
    }}
