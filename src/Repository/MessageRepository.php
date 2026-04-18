<?php

namespace App\Repository;

use App\Entity\Message;
use App\Entity\Equipe;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class MessageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Message::class);
    }

    public function findMessagesByTeam(Equipe $equipe, int $limit = 50): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.equipe = :equipe')
            ->andWhere('m.estSupprime = :estSupprime')
            ->setParameter('equipe', $equipe)
            ->setParameter('estSupprime', false)
            ->orderBy('m.dateEnvoi', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function findRecentMessagesByTeam(Equipe $equipe, int $lastMessageId = null): array
    {
        $qb = $this->createQueryBuilder('m')
            ->andWhere('m.equipe = :equipe')
            ->andWhere('m.estSupprime = :estSupprime')
            ->setParameter('equipe', $equipe)
            ->setParameter('estSupprime', false)
            ->orderBy('m.dateEnvoi', 'ASC');
        
        if ($lastMessageId) {
            $qb->andWhere('m.idMessage > :lastId')
               ->setParameter('lastId', $lastMessageId);
        }
        
        return $qb->getQuery()->getResult();
    }
}