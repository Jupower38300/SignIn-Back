<?php

namespace App\Repository;

use App\Entity\Presences;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Sessions;

/**
 * @extends ServiceEntityRepository<Presences>
 */
class PresencesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Presences::class);
    }

    public function findPresencesForSession(Sessions $session): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.session = :session')
            ->setParameter('session', $session)
            ->join('p.user', 'u')
            ->addSelect('u')
            ->orderBy('p.dateConnexion', 'DESC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Presences[] Returns an array of Presences objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Presences
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
