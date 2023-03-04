<?php

namespace App\Controller;

use App\Entity\Concedii;
use App\Entity\User;
use App\Form\ConcediiType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use DateTime;

#[Route('/user')]
class ConcediuController extends AbstractController
{
    #[Route('/concedii', name: 'app_concediu', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('concediu/index.html.twig');
    }

    #[Route('/concedii/new', name: 'app_concediu_new', methods: ['GET', 'POST'])]
    public function addConcediu(Request $request, EntityManagerInterface $entityManager, Security $security): Response
    {
        $form = $this->createForm(ConcediiType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $concediu = new Concedii();

            /** @var Concedii $concediu */
            /** @var User $user */

            $user = $security->getUser();

            $concediu->setUserId($user);
            $startDate = $form["startDate"]->getData();
            $endDate = $form["endDate"]->getData();
            $details = $form["details"]->getData();
            $concediu->setStartDate($startDate);
            $concediu->setEndDate($endDate);
            $concediu->setDetails($details);
            $concediu->setCreated(new DateTime());
            $concediu->setStatus('pending');
            $entityManager->persist($concediu);
            $entityManager->flush();

            $this->addFlash('success', 'Cererea ta a fost trimisa la verificare!');

            return $this->redirectToRoute('app_concediu');
        }

        return $this->render('concediu/new.html.twig', [
            'form' => $form
        ]);

    }

    #[Route('/concedii/concediile-tale', name: 'app_concediu_showconcedii')]
    public function showConcedii(Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = $security->getUser();
        $concedii = $entityManager->getRepository(Concedii::class)->findBy(['user' => $user]);
        return $this->render('concediu/show.html.twig',[
            'concedii' => $concedii
            ]);
    }

    #[Route('concedii/{id}/response', name: 'app_concediu_detailed')]
    public function concediuDetailed(Concedii $id): Response
    {
        return $this->render('concediu/concediuDetailed.html.twig', [
            'concedii' => $id
        ]);
    }
}
