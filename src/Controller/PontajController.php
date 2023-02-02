<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user')]
class PontajController extends AbstractController
{
    #[Route('/pontaje', name: 'app_pontaj')]
    public function index(): Response
    {
        return $this->render('pontaj/index.html.twig', [
            'controller_name' => 'PontajController',
        ]);
    }
}
