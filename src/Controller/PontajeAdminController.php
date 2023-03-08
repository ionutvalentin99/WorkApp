<?php

namespace App\Controller;

use App\Repository\PontajeRepository;
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
}
