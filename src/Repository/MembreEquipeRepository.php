<?php

namespace App\Repository;

use App\Entity\MembreEquipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MembreEquipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MembreEquipe::class);
    }

    /**
     * Membres d'une équipe avec filtres
     */
    public function findByEquipeWithFilters(
        int $equipeId,
        string $q = '',
        string $role = '',
        string $statut = '',
        string $sortBy = 'dateAffectation',
        string $sortDir = 'DESC'
    ): array {
        $qb = $this->createQueryBuilder('m')
            ->where('m.equipe = :equipeId')
            ->setParameter('equipeId', $equipeId);

        if ($q) {
            $qb->andWhere('m.competencesPrincipales LIKE :q OR m.notes LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }

        if ($role) {
            $qb->andWhere('m.roleEquipe = :role')
               ->setParameter('role', $role);
        }

        if ($statut) {
            $qb->andWhere('m.statutMembre = :statut')
               ->setParameter('statut', $statut);
        }

        $allowedSorts = ['dateAffectation', 'roleEquipe', 'statutMembre', 'tauxParticipation'];
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'dateAffectation';
        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy('m.' . $sortBy, $sortDir);

        return $qb->getQuery()->getResult();
    }

    /**
     * Vérifie si un utilisateur est déjà dans une équipe
     */
    public function isUserInEquipe(int $userId, int $equipeId): bool
    {
        $count = (int) $this->createQueryBuilder('m')
            ->select('COUNT(m.idMembre)')
            ->where('m.idUser = :userId')
            ->andWhere('m.equipe = :equipeId')
            ->setParameter('userId', $userId)
            ->setParameter('equipeId', $equipeId)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }
}
