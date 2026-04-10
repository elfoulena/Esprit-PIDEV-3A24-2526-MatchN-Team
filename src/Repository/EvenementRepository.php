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
}
