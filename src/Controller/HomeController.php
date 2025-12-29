<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\WorkRepository;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(WorkRepository $pontajeRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $isVerified = $user?->isVerified();
        $lastUserWorkRecords = null;

        if ($user) {
            $lastUserWorkRecords = $pontajeRepository->getLastWorkRecords($user->getId());
        }

        return $this->render('home/index.html.twig', [
            'user' => $user,
            'isVerified' => $isVerified,
            'pontaje' => $lastUserWorkRecords,
            'date' => (new DateTime())->format('Y-m-d')
        ]);
    }
}
