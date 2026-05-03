<?php

namespace App\Repository;

use App\Entity\TeamRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamRequest>
 */
class TeamRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamRequest::class);
    }

    /**
     * @return array<int, TeamRequest>
     */
    public function findPendingByTeam(int $teamId): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.team = :teamId')
            ->andWhere('t.status = :status')
            ->setParameter('teamId', $teamId)
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, TeamRequest>
     */
    public function findPendingByEmployee(int $employeeId): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.employee = :employeeId')
            ->andWhere('t.status = :status')
            ->setParameter('employeeId', $employeeId)
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<int, TeamRequest>
     */
    public function findRequestsByEmployee(int $employeeId): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.employee = :employeeId')
            ->setParameter('employeeId', $employeeId)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countPendingRequests(): int
    {
        return (int) $this->createQueryBuilder('t')
            ->select('COUNT(t.id)')
            ->where('t.status = :status')
            ->setParameter('status', 'pending')
            ->getQuery()
            ->getSingleScalarResult();
    }
}