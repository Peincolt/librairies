<?php

namespace App\Repository;

use App\Entity\UserDemand;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method UserDemand|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserDemand|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserDemand[]    findAll()
 * @method UserDemand[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserDemandRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserDemand::class);
    }

    // /**
    //  * @return UserDemand[] Returns an array of UserDemand objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?UserDemand
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
