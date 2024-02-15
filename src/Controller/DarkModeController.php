<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DarkModeController extends AbstractController
{
    #[Route('/toggle-dark', name: 'app_dark_mode')]
    public function toggleMode(Request $request): Response
    {
        $isDarkMode = $request->request->get('isDarkMode');

        return new JsonResponse(['isDarkMode' => $isDarkMode]);
    }
}
