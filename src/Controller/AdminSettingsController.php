<?php

namespace App\Controller;

use App\Service\AppSettingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/settings')]
class AdminSettingsController extends AbstractController
{
    public function __construct(
        private readonly AppSettingService $settingService,
    ) {}

    #[Route('', name: 'app_admin_settings', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/settings.html.twig', [
            'registration_enabled' => $this->settingService->isRegistrationEnabled(),
        ]);
    }

    #[Route('/toggle-registration', name: 'app_admin_toggle_registration', methods: ['POST'])]
    public function toggleRegistration(Request $request): Response
    {
        $enabled = $request->request->getBoolean('registration_enabled');
        $this->settingService->setBool('registration_enabled', $enabled);

        $this->addFlash(
            'success',
            $enabled ? 'Înregistrările au fost activate.' : 'Înregistrările au fost dezactivate.'
        );

        return $this->redirectToRoute('app_admin_settings');
    }
}
