<?php

namespace App\Repository;

use App\Entity\IpmReportByHostnameAndServer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmReportByHostnameAndServer>
 *
 * @method IpmReportByHostnameAndServer|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmReportByHostnameAndServer|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmReportByHostnameAndServer[]    findAll()
 * @method IpmReportByHostnameAndServer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmReportByHostnameAndServerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmReportByHostnameAndServer::class);
    }

//    /**
//     * @return IpmReportByHostnameAndServer[] Returns an array of IpmReportByHostnameAndServer objects
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

//    public function findOneBySomeField($value): ?IpmReportByHostnameAndServer
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
