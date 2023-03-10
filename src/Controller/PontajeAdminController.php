<?php

namespace App\Controller;

use App\Entity\Pontaje;
use App\Repository\PontajeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PontajeAdminController extends AbstractController
{
    #[Route('/admin/pontaje', name: 'app_pontaje_admin')]
    public function showPontaje(PontajeRepository $pontajeRepository): Response
    {
        return $this->render('pontaje_admin/index.html.twig', [
            'pontaje' => $pontajeRepository->findAll(),
        ]);
    }

    #[Route('/pontaje/delete/{id}', name: 'app_pontaj_admin_delete')]
    public function deletePontaj(Pontaje $pontaje, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($pontaje);
        $entityManager->flush();
        return $this->redirectToRoute('app_pontaje_admin');
    }
}
