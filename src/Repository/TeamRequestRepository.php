<?php

namespace App\Repository;

use App\Entity\TeamRequest;
use App\Entity\User;
use App\Entity\Equipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TeamRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamRequest::class);
    }

    public function findPendingByTeam(Equipe $team): array
    {
        return $this->createQueryBuilder('tr')
            ->andWhere('tr.team = :team')
            ->andWhere('tr.status = :status')
            ->setParameter('team', $team)
            ->setParameter('status', 'pending')
            ->orderBy('tr.createdAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findPendingByEmployee(User $employee): array
    {
        return $this->createQueryBuilder('tr')
            ->andWhere('tr.employee = :employee')
            ->andWhere('tr.status = :status')
            ->setParameter('employee', $employee)
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getResult();
    }

    public function findRequestsByEmployee(User $employee): array
    {
        return $this->createQueryBuilder('tr')
            ->andWhere('tr.employee = :employee')
            ->setParameter('employee', $employee)
            ->orderBy('tr.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countPendingRequests(): int
    {
        return $this->createQueryBuilder('tr')
            ->select('COUNT(tr.id)')
            ->andWhere('tr.status = :status')
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getSingleScalarResult();
    }
}