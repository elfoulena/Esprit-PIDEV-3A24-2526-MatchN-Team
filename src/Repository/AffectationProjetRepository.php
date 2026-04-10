<?php

namespace App\Repository;

use App\Entity\AffectationProjet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AffectationProjet>
 */
class AffectationProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AffectationProjet::class);
    }

    /**
     * @return AffectationProjet[]
     */
    public function findAllWithDetails(): array
    {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.User', 'u')
            ->addSelect('u')
            ->leftJoin('a.projet', 'p')
            ->addSelect('p')
            ->orderBy('a.date_debut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByStatut(): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.statut, COUNT(a.id) as total')
            ->groupBy('a.statut')
            ->getQuery()
            ->getResult();
    }
}
