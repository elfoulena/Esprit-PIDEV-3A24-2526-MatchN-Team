<?php

namespace App\Controller\Api;

use App\Entity\Evenement;
use App\Repository\EvenementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/events')]
class EventApiController extends AbstractController
{
    public function __construct(private EvenementRepository $eventRepository) {}

    #[Route('', name: 'api_events_index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $start = $request->query->get('start');
        $end = $request->query->get('end');
        $type = $request->query->get('type');
        $status = $request->query->get('status');
        $equipeId = $request->query->get('equipe_id');

        $qb = $this->eventRepository->createQueryBuilder('e');

        if ($start) {
            $qb->andWhere('e.date_debut >= :start')
               ->setParameter('start', new \DateTime($start));
        }

        if ($end) {
            $qb->andWhere('e.date_debut <= :end')
               ->setParameter('end', new \DateTime($end));
        }

        if ($type) {
            $qb->andWhere('e.type_evenement = :type')
               ->setParameter('type', $type);
        }

        if ($status) {
            $qb->andWhere('e.statut = :status')
               ->setParameter('status', $status);
        }

        $events = $qb->getQuery()->getResult();

        $formattedEvents = array_map(function(Evenement $event) {
            return [
                'id' => $event->getIdEvenement(),
                'title' => $event->getTitre(),
                'start' => $event->getDateDebut()->format('Y-m-d\TH:i:s'),
                'end' => $event->getDateFin()->format('Y-m-d\TH:i:s'),
                'description' => $event->getDescription(),
                'lieu' => $event->getLieu(),
                'type' => $event->getTypeEvenement(),
                'statut' => $event->getStatut(),
                'capacite_max' => $event->getCapaciteMax(),
                'nombre_actuel' => $event->getNombreActuel(),
                'backgroundColor' => $this->getEventColor($event->getTypeEvenement()),
                'borderColor' => $this->getEventColor($event->getTypeEvenement()),
                'textColor' => '#ffffff',
            ];
        }, $events);

        return $this->json($formattedEvents);
    }

    #[Route('/{id}', name: 'api_events_show', methods: ['GET'])]
    public function show(Evenement $event): JsonResponse
    {
        return $this->json([
            'id' => $event->getIdEvenement(),
            'title' => $event->getTitre(),
            'start' => $event->getDateDebut()->format('Y-m-d\TH:i:s'),
            'end' => $event->getDateFin()->format('Y-m-d\TH:i:s'),
            'description' => $event->getDescription(),
            'lieu' => $event->getLieu(),
            'type' => $event->getTypeEvenement(),
            'statut' => $event->getStatut(),
            'capacite_max' => $event->getCapaciteMax(),
            'nombre_actuel' => $event->getNombreActuel(),
            'date_creation' => $event->getDateCreation()?->format('Y-m-d H:i:s'),
            'date_deadline' => $event->getDateDeadline()?->format('Y-m-d H:i:s'),
        ]);
    }

    #[Route('/stats', name: 'api_events_stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        $now = new \DateTime();
        
        $total = $this->eventRepository->createQueryBuilder('e')
            ->select('COUNT(e.id_evenement)')
            ->getQuery()
            ->getSingleScalarResult();

        $upcoming = $this->eventRepository->createQueryBuilder('e')
            ->select('COUNT(e.id_evenement)')
            ->where('e.date_debut > :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult();

        $ongoing = $this->eventRepository->createQueryBuilder('e')
            ->select('COUNT(e.id_evenement)')
            ->where('e.date_debut <= :now AND e.date_fin >= :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult();

        $completed = $this->eventRepository->createQueryBuilder('e')
            ->select('COUNT(e.id_evenement)')
            ->where('e.date_fin < :now')
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult();

        $byType = $this->eventRepository->createQueryBuilder('e')
            ->select('e.type_evenement as type, COUNT(e.id_evenement) as count')
            ->groupBy('e.type_evenement')
            ->getQuery()
            ->getResult();

        return $this->json([
            'total' => $total,
            'upcoming' => $upcoming,
            'ongoing' => $ongoing,
            'completed' => $completed,
            'byType' => $byType
        ]);
    }

    private function getEventColor(?string $type): string
    {
        return match($type) {
            'CONFERENCE' => '#3498db',
            'WORKSHOP' => '#2ecc71',
            'COMPETITION' => '#e74c3c',
            'FORMATION' => '#9b59b6',
            'MEETUP' => '#f39c12',
            'TEAM_BUILDING' => '#1abc9c',
            'EVENT_INTERNE' => '#34495e',
            'PORTE_OUVERTE' => '#e67e22',
            default => '#95a5a6',
        };
    }
}