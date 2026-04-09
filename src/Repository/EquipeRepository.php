<?php

namespace App\Repository;

use App\Entity\Equipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EquipeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Equipe::class);
    }

    /**
     * Recherche + tri + filtrage avancé
     */
    public function findWithFilters(
        string $q = '',
        string $statut = '',
        string $departement = '',
        string $sortBy = 'dateCreation',
        string $sortDir = 'DESC'
    ): array {
        $qb = $this->createQueryBuilder('e');

        if ($q) {
            $qb->andWhere('e.nomEquipe LIKE :q OR e.description LIKE :q OR e.departement LIKE :q')
               ->setParameter('q', '%' . $q . '%');
        }

        if ($statut) {
            $qb->andWhere('e.statut = :statut')
               ->setParameter('statut', $statut);
        }

        if ($departement) {
            $qb->andWhere('e.departement = :departement')
               ->setParameter('departement', $departement);
        }

        $allowedSorts = ['nomEquipe', 'dateCreation', 'statut', 'nbMembresActuel', 'budget', 'departement'];
        $sortBy = in_array($sortBy, $allowedSorts) ? $sortBy : 'dateCreation';
        $sortDir = strtoupper($sortDir) === 'ASC' ? 'ASC' : 'DESC';

        $qb->orderBy('e.' . $sortBy, $sortDir);

        return $qb->getQuery()->getResult();
    }

    /**
     * Statistiques globales
     */
    public function getStats(): array
    {
        $total = $this->count([]);
        $active = $this->count(['statut' => 'Active']);
        $inactive = $this->count(['statut' => 'Inactive']);
        $pause = $this->count(['statut' => 'En pause']);

        $totalMembres = (int) $this->createQueryBuilder('e')
            ->select('SUM(e.nbMembresActuel)')
            ->getQuery()
            ->getSingleScalarResult();

        return [
            'total' => $total,
            'active' => $active,
            'inactive' => $inactive,
            'pause' => $pause,
            'totalMembres' => $totalMembres,
        ];
    }

    /**
     * Liste de tous les départements distincts
     */
    public function findDistinctDepartements(): array
    {
        return $this->createQueryBuilder('e')
            ->select('DISTINCT e.departement')
            ->where('e.departement IS NOT NULL')
            ->orderBy('e.departement', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }
}
