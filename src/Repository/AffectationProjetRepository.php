<?php

namespace App\Repository;

use App\Entity\AffectationProjet;
use App\Entity\User;
use App\Enum\Role;
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

    /**
     * @return list<array{statut: string|null, total: int|string}>
     */
    public function countByStatut(): array
    {
        return $this->createQueryBuilder('a')
            ->select('a.statut, COUNT(a.id) as total')
            ->groupBy('a.statut')
            ->getQuery()
            ->getResult();
    }

    public function updateExpiredAffectations(): int
    {
        return $this->createQueryBuilder('a')
            ->update()
            ->set('a.statut', ':newStatut')
            ->where('a.statut = :currentStatut')
            ->andWhere('a.date_fin IS NOT NULL')
            ->andWhere('a.date_fin < :today')
            ->setParameter('newStatut', 'TERMINEE')
            ->setParameter('currentStatut', 'ACCEPTEE')
            ->setParameter('today', new \DateTime('today'))
            ->getQuery()
            ->execute();
    }

    /**
     * Returns leaderboard data: freelancers ranked by score.
     * Score = (avgNote * 6) + (finishedProjects * 4)
     *
     * @return array<array{id: int, nom: string, prenom: string, email: string, avgNote: float, finishedProjects: int, score: float}>
     */
    public function getLeaderboardData(): array
    {
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();

        $results = $qb
            ->select(
                'u.id',
                'u.nom',
                'u.prenom',
                'u.email',
                'COALESCE(AVG(e.note), 0) AS avgNote',
                'SUM(CASE WHEN a.statut = :terminee THEN 1 ELSE 0 END) AS finishedProjects',
                '(COALESCE(AVG(e.note), 0) * 6) + (SUM(CASE WHEN a.statut = :terminee THEN 1 ELSE 0 END) * 4) AS score'
            )
            ->from(User::class, 'u')
            ->innerJoin(AffectationProjet::class, 'a', 'WITH', 'a.User = u')
            ->leftJoin('a.evaluationPartTimes', 'e')
            ->where('u.role = :role')
            ->setParameter('role', Role::FREELANCER)
            ->setParameter('terminee', 'TERMINEE')
            ->groupBy('u.id, u.nom, u.prenom, u.email')
            ->orderBy('score', 'DESC')
            ->getQuery()
            ->getResult();

        return $results;
    }
}
