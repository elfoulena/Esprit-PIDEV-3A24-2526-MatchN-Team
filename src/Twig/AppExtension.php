<?php

namespace App\Twig;

use App\Repository\TeamRequestRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
    private $teamRequestRepository;

    public function __construct(TeamRequestRepository $teamRequestRepository)
    {
        $this->teamRequestRepository = $teamRequestRepository;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('admin_team_requests_pending_count', [$this, 'getPendingRequestsCount']),
        ];
    }

    public function getPendingRequestsCount(): int
    {
        return $this->teamRequestRepository->countPendingRequests();
    }
}