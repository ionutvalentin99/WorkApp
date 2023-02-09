<?php

namespace App\Controller;

use App\Entity\Concedii;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/concedii')]
class PendingController extends AbstractController
{
    #[Route('/pending', name: 'app_pending_concedii')]
    public function pendingConcedii(EntityManagerInterface $entityManager): Response
    {
        $concedii = $entityManager->getRepository(Concedii::class)->findBy(['status' => 'pending']);

        return $this->render('concediu/pending.html.twig', [
            'concedii' => $concedii,
        ]);
    }

    #[Route('/{id}/response', name: 'app_pending_show_concediu')]
    public function showOne(Concedii $id): Response
    {

        return $this->render('concediu/showOne.html.twig', [
            'concedii' => $id
        ]);
    }

    #[Route('/{id}/response/approved', name: 'app_pending_approved')]
    public function approved(Concedii $concedii, EntityManagerInterface $entityManager): Response
    {
        $concedii->setStatus('approved');
        $entityManager->flush();
        return $this->redirectToRoute('app_pending_concedii');
    }

    #[Route('/{id}/response/deny', name: 'app_pending_denied')]
    public function deny(Concedii $concedii, EntityManagerInterface $entityManager): Response
    {
        $concedii->setStatus('denied');
        $entityManager->flush();
        return $this->redirectToRoute('app_pending_concedii');
    }
}
