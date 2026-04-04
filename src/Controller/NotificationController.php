<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class NotificationController extends AbstractController
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly NotificationRepository $notificationRepository,
    ) {}

    #[Route('/user/notifications', name: 'app_notifications')]
    public function index(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $notifications = $this->notificationRepository->findRecentByUser($user, 50);
        $this->notificationService->markAllRead($user);

        return $this->render('notifications/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    #[Route('/user/notifications/{id}/read', name: 'app_notification_read', methods: ['POST'])]
    public function markRead(Notification $notification, EntityManagerInterface $em): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        if ($notification->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }

        $this->notificationService->markRead($notification);

        $redirect = $notification->getLink() ?? $this->generateUrl('app_notifications');
        return $this->redirect($redirect);
    }
}
