<?php

namespace App\Twig;

use App\Entity\User;
use App\Repository\MembreEquipeRepository;
use App\Repository\MessageRepository;
use App\Repository\NotificationRepository;
use App\Repository\TeamRequestRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private $teamRequestRepository;
    private $membreEquipeRepository;
    private $messageRepository;
    private $notificationRepository;

    public function __construct(
        TeamRequestRepository $teamRequestRepository,
        MembreEquipeRepository $membreEquipeRepository,
        MessageRepository $messageRepository,
        NotificationRepository $notificationRepository
    ) {
        $this->teamRequestRepository = $teamRequestRepository;
        $this->membreEquipeRepository = $membreEquipeRepository;
        $this->messageRepository = $messageRepository;
        $this->notificationRepository = $notificationRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_team_requests_pending_count', [$this, 'getPendingRequestsCount']),
            new TwigFunction('get_user_team', [$this, 'getUserTeam']),
            new TwigFunction('get_unread_messages_count', [$this, 'getUnreadMessagesCount']),
            new TwigFunction('unread_notifications_count', [$this, 'getUnreadNotificationsCount']),
            new TwigFunction('latest_notifications', [$this, 'getLatestNotifications']),
        ];
    }

    public function getPendingRequestsCount(): int
    {
        return $this->teamRequestRepository->countPendingRequests();
    }

    public function getUserTeam(?User $user)
    {
        if (!$user) {
            return null;
        }
        
        $membre = $this->membreEquipeRepository->findOneBy([
            'user' => $user, 
            'statutMembre' => 'Actif'
        ]);
        
        return $membre ? $membre->getEquipe() : null;
    }

    public function getUnreadMessagesCount(?User $user, ?int $teamId): int
    {
        if (!$user || !$teamId) {
            return 0;
        }
        
        // Get last read message timestamp from session or database
        // For now, return 0. You can implement this later with a read receipts table
        return 0;
    }

    public function getUnreadNotificationsCount(?User $user): int
    {
        if (!$user) {
            return 0;
        }

        return $this->notificationRepository->countUnreadForUser($user);
    }

    public function getLatestNotifications(?User $user, int $limit = 6): array
    {
        if (!$user) {
            return [];
        }

        return $this->notificationRepository->findLatestForUser($user, $limit);
    }
}
