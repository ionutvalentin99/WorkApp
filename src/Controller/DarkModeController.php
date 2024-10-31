<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class DarkModeController extends AbstractController
{
    #[Route('/toggle-dark-mode', name: 'app_dark_mode')]
    public function toggleMode(SessionInterface $session): Response
    {
        $darkMode = $session->get('darkMode', true);
        $darkMode = !$darkMode;
        $session->set('darkMode', $darkMode);

        return $this->json(['darkMode' => $darkMode]);
    }
}
