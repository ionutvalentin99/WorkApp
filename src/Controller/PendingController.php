<?php

namespace App\Controller;

use App\Entity\Concedii;
use App\Repository\ConcediiRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/concedii')]
class PendingController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly ConcediiRepository $concediiRepository)
    {
    }

    #[Route('/pending', name: 'app_pending_concedii')]
    public function pendingConcedii(): Response
    {
        $pendingHolidays = $this->concediiRepository->getPendingHolidaysAsc();
        $count = count($pendingHolidays);

        return $this->render('concediu/pending.html.twig', [
            'concedii' => $pendingHolidays,
            'count' => $count,
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
    public function approved(Concedii $concedii): Response
    {
        $concedii->setStatus('approved');
        $this->entityManager->flush();

        return $this->redirectToRoute('app_pending_concedii');
    }

    #[Route('/{id}/response/deny', name: 'app_pending_denied')]
    public function deny(Concedii $concedii): Response
    {
        $details = $_POST['deny'];
        $concedii->setDetails($details);
        $concedii->setStatus('denied');
        $this->entityManager->flush();

        return $this->redirectToRoute('app_pending_concedii');
    }

}
