<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;

class NotificationService
{
    public function __construct(
        private readonly NotificationRepository $notificationRepository,
        private readonly EntityManagerInterface $em,
    ) {}

    public function notify(User $user, string $message, string $type = 'info', ?string $link = null): void
    {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setMessage($message);
        $notification->setType($type);
        $notification->setLink($link);

        $this->em->persist($notification);
        $this->em->flush();
    }

    public function getUnreadCount(User $user): int
    {
        return $this->notificationRepository->countUnreadByUser($user);
    }

    public function getRecent(User $user, int $limit = 10): array
    {
        return $this->notificationRepository->findRecentByUser($user, $limit);
    }

    public function markAllRead(User $user): void
    {
        $this->notificationRepository->markAllReadForUser($user);
    }

    public function markRead(Notification $notification): void
    {
        $notification->setIsRead(true);
        $this->em->flush();
    }
}
