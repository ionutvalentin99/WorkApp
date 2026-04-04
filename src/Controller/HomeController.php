<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\WorkRepository;
use App\Service\ActiveCompanyService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly WorkRepository $pontajeRepository,
        private readonly ActiveCompanyService $activeCompanyService
    )
    {
    }
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $isVerified = $user?->isVerified();
        $lastUserWorkRecords = null;
        $activeCompany = $this->activeCompanyService->getActiveCompany();

        if ($user && $activeCompany) {
            $lastUserWorkRecords = $this->pontajeRepository->getLastWorkRecords($user->getId(), $activeCompany->getId());
        }
        return $this->render('home/index.html.twig', [
            'user' => $user,
            'isVerified' => $isVerified,
            'pontaje' => $lastUserWorkRecords,
            'date' => (new DateTime())->format('Y-m-d')
        ]);
    }
}
