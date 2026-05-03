<?php
namespace App\Repository;

use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reclamation>
 */
class ReclamationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reclamation::class);
    }

    /** @return array<int, Reclamation> */
    public function findWithFilters(?string $statut, ?string $search): array
    {
        $qb = $this->createQueryBuilder('r');
        if ($statut) {
            $qb->andWhere('r.statut = :statut')->setParameter('statut', $statut);
        }
        if ($search) {
            $qb->andWhere('r.message LIKE :search')->setParameter('search', '%'.$search.'%');
        }
        return $qb->orderBy('r.dateCreation', 'DESC')->getQuery()->getResult();
    }

    /** @return array<int, Reclamation> */
    public function findByUserWithFilters(int $userId, ?string $statut, ?string $search): array
    {
        $qb = $this->createQueryBuilder('r')
            ->andWhere('r.utilisateurId = :userId')
            ->setParameter('userId', $userId);
        if ($statut) {
            $qb->andWhere('r.statut = :statut')->setParameter('statut', $statut);
        }
        if ($search) {
            $qb->andWhere('r.message LIKE :search')->setParameter('search', '%'.$search.'%');
        }
        return $qb->orderBy('r.dateCreation', 'DESC')->getQuery()->getResult();
    }
}