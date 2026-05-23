<?php

namespace App\Repository;

use App\Entity\IpmTagInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmTagInfo>
 *
 * @method IpmTagInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmTagInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmTagInfo[]    findAll()
 * @method IpmTagInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmTagInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmTagInfo::class);
    }

//    /**
//     * @return IpmTagInfo[] Returns an array of IpmTagInfo objects
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

//    public function findOneBySomeField($value): ?IpmTagInfo
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
