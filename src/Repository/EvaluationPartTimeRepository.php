<?php

namespace App\Repository;

use App\Entity\EvaluationPartTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EvaluationPartTime>
 */
class EvaluationPartTimeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EvaluationPartTime::class);
    }

    /**
     * @return EvaluationPartTime[]
     */
    public function findAllWithDetails(): array
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.affectationProjet', 'a')
            ->addSelect('a')
            ->leftJoin('a.User', 'u')
            ->addSelect('u')
            ->leftJoin('a.projet', 'p')
            ->addSelect('p')
            ->orderBy('e.date_evaluation', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function existePourAffectation(int $affectationId): bool
    {
        $count = $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.affectationProjet = :id')
            ->setParameter('id', $affectationId)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
