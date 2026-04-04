<?php

namespace App\Twig;

use App\Entity\User;
use App\Service\NotificationService;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class NotificationExtension extends AbstractExtension
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly Security $security,
    ) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('notification_count', $this->getCount(...)),
            new TwigFunction('recent_notifications', $this->getRecent(...)),
        ];
    }

    public function getCount(): int
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return 0;
        }
        return $this->notificationService->getUnreadCount($user);
    }

    public function getRecent(): array
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return [];
        }
        return $this->notificationService->getRecent($user);
    }
}
