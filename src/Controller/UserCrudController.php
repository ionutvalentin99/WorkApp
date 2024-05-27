<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserEditType;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

#[Route('/admin/crud')]
class UserCrudController extends AbstractController
{
    #[Route('/', name: 'app_user_crud_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user_crud/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_crud_new')]
    public function new(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        {
            $user = new User();
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $hashedPassword = $passwordHasher->hashPassword($user, $form->getData()->getPassword());
                $user->setPassword($hashedPassword);
                $user->setCreated(new DateTime());

                $entityManager->persist($user);
                $entityManager->flush();

                return $this->redirectToRoute('app_user_crud_index', [], Response::HTTP_SEE_OTHER);
            }

            return $this->render('user_crud/new.html.twig', [
                'form' => $form->createView(),
            ]);
        }
    }

    #[Route('/{id}', name: 'app_user_crud_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user_crud/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_crud_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $userCurrentEmail = $user->getEmail();
        $form = $this->createForm(UserEditType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userFormData = $form->getData();
            if ($userFormData->getEmail() !== $userCurrentEmail) {
                $user->setIsVerified(false);
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_user_crud_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user_crud/edit.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_user_crud_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_crud_index', [], Response::HTTP_SEE_OTHER);
    }
}
