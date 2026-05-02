<?php

namespace App\Repository;

use App\Entity\CompetenceF;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CompetenceF>
 */
class CompetenceFRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompetenceF::class);
    }

    public function findByName(string $name): ?CompetenceF
    {
        return $this->createQueryBuilder('c')
            ->where('LOWER(c.nom) = :nom')
            ->setParameter('nom', strtolower($name))
            ->getQuery()
            ->getOneOrNullResult();
    }
}