<?php

namespace App\Repository;

use App\Entity\IpmReportByHostname;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmReportByHostname>
 *
 * @method IpmReportByHostname|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmReportByHostname|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmReportByHostname[]    findAll()
 * @method IpmReportByHostname[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmReportByHostnameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmReportByHostname::class);
    }

//    /**
//     * @return IpmReportByHostname[] Returns an array of IpmReportByHostname objects
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

//    public function findOneBySomeField($value): ?IpmReportByHostname
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
