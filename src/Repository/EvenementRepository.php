<?php

namespace App\Repository;

use App\Entity\Evenement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Evenement>
 */
class EvenementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evenement::class);
    }

    /**
     * General filters for front-office/visitors
     * @return array<int, Evenement>
     */
    public function findByFilters(?string $q, ?string $type, ?string $sort): array
    {
        $qb = $this->createQueryBuilder('e');
        $now = new \DateTime();

        $qb->andWhere('e.date_fin > :now')
           ->setParameter('now', $now);

        if ($q) {
            $qb->andWhere('e.titre LIKE :q OR e.lieu LIKE :q')
               ->setParameter('q', '%'.$q.'%');
        }

        if ($type && $type !== 'ALL') {
            $qb->andWhere('e.type_evenement = :type')
               ->setParameter('type', $type);
        }

        switch ($sort) {
            case 'date_asc':
                $qb->orderBy('e.date_debut', 'ASC');
                break;
            case 'date_desc':
                $qb->orderBy('e.date_debut', 'DESC');
                break;
            case 'titre_asc':
                $qb->orderBy('e.titre', 'ASC');
                break;
            default:
                $qb->orderBy('e.date_debut', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<int, Evenement>
     */
    public function findByEmployeFilters(?string $q, ?string $type, ?string $sort, string $filter, int $userId): array
    {
        $qb = $this->createQueryBuilder('e');
        $now = new \DateTime();

        // Participation filter logic
        if ($filter === 'JOINED' || $filter === 'HISTORY') {
            $qb->join('e.participations', 'p')
               ->andWhere('p.utilisateur = :userId')
               ->setParameter('userId', $userId);
        }

        if ($filter === 'HISTORY') {
            // Already happened
            $qb->andWhere('e.date_fin < :now')
               ->setParameter('now', $now);
        } else {
            // Only upcoming by default (unless history requested)
            $qb->andWhere('e.date_fin > :now')
               ->setParameter('now', $now);
        }

        if ($q) {
            $qb->andWhere('e.titre LIKE :q OR e.lieu LIKE :q')
               ->setParameter('q', '%'.$q.'%');
        }

        if ($type && $type !== 'ALL') {
            $qb->andWhere('e.type_evenement = :type')
               ->setParameter('type', $type);
        }

        switch ($sort) {
            case 'date_asc':
                $qb->orderBy('e.date_debut', 'ASC');
                break;
            case 'date_desc':
                $qb->orderBy('e.date_debut', 'DESC');
                break;
            case 'titre_asc':
                $qb->orderBy('e.titre', 'ASC');
                break;
            default:
                $qb->orderBy('e.date_debut', 'ASC');
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<int, Evenement>
     */
    public function findBySearch(?string $q, bool $includeArchived = false): array
    {
        $qb = $this->createQueryBuilder('e');
        if ($q) {
            $qb->andWhere('e.titre LIKE :q OR e.lieu LIKE :q')
               ->setParameter('q', '%'.$q.'%');
        } elseif (!$includeArchived) {
            // Hide finished events by default in back-office if not searching or requesting archives
            $now = new \DateTime();
            $qb->andWhere('e.date_fin > :now')
               ->setParameter('now', $now);
        }
        $qb->orderBy('e.date_debut', 'ASC');
        return $qb->getQuery()->getResult();
    }

    /**
     * @return array<string, mixed>
     */
    public function getStatisticsForAI(): array
    {
        $qb = $this->createQueryBuilder('e');
        
        $totalEvenements = (int) $qb->select('COUNT(e.id_evenement)')->getQuery()->getSingleScalarResult();
        
        $typesData = $this->createQueryBuilder('e')
            ->select('e.type_evenement, COUNT(e.id_evenement) as count')
            ->groupBy('e.type_evenement')
            ->getQuery()
            ->getResult();
            
        $avgFillRate = 0;
        if ($totalEvenements > 0) {
            $avgFillRateResult = $this->createQueryBuilder('e')
                ->select('AVG(e.nombre_actuel / e.capacite_max) * 100')
                ->where('e.capacite_max > 0')
                ->getQuery()
                ->getSingleScalarResult();
            $avgFillRate = $avgFillRateResult ? round((float)$avgFillRateResult, 2) : 0;
        }

        return [
            'total_evenements' => $totalEvenements,
            'repartition_types' => $typesData,
            'taux_remplissage_moyen_pourcentage' => $avgFillRate,
        ];
    }
}
