<?php

namespace App\Repository;

use App\Entity\IpmTimer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmTimer>
 *
 * @method IpmTimer|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmTimer|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmTimer[]    findAll()
 * @method IpmTimer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmTimerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmTimer::class);
    }

//    /**
//     * @return IpmTimer[] Returns an array of IpmTimer objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('i.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?IpmTimer
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
