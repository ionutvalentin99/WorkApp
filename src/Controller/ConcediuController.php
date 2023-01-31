<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConcediuController extends AbstractController
{
    #[Route('/concedii', name: 'app_concediu')]
    public function index(): Response
    {
        return $this->render('concediu/index.html.twig');
    }
}
