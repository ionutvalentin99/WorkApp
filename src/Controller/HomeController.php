<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser()?->getFirstName();

        return $this->render('home/index.html.twig', [
        'user' => $user
        ]);
    }
}
