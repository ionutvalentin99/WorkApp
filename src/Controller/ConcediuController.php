<?php

namespace App\Controller;

use App\Entity\Concedii;
use App\Form\ConcediiType;
use App\Repository\ConcediiRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class ConcediuController extends AbstractController
{
    #[Route('/concedii', name: 'app_concediu', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('concediu/index.html.twig');
    }

    #[Route('/concedii/new', name: 'app_concediu_new', methods: ['GET', 'POST'])]
    public function addConcediu(Request $request, EntityManagerInterface $entityManager): Response
    {
        $concediu = new Concedii();
        $form = $this->createForm(ConcediiType::class, $concediu);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $entityManager->persist($concediu);
            $entityManager->flush();

            return $this->redirectToRoute('app_concediu');
        }

        return $this->render('concediu/new.html.twig', [
        //'concediu' => $concediu,
            'form' => $form
        ]);

    }

    #[Route('/concedii/concediile-tale', name: 'app_concediu_showconcedii', methods: ['GET'])]
    public function showConcedii(ConcediiRepository $concediiRepository): Response
    {
        return $this->render('concediu/show.html.twig', [
            'concedii' => $concediiRepository->findAll(),
        ]);
    }

}
