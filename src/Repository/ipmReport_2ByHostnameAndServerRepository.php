<?php

namespace App\Repository;

use App\Entity\ipmReport_2ByHostnameAndServer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ipmReport_2ByHostnameAndServer>
 *
 * @method ipmReport_2ByHostnameAndServer|null find($id, $lockMode = null, $lockVersion = null)
 * @method ipmReport_2ByHostnameAndServer|null findOneBy(array $criteria, array $orderBy = null)
 * @method ipmReport_2ByHostnameAndServer[]    findAll()
 * @method ipmReport_2ByHostnameAndServer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ipmReport_2ByHostnameAndServerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ipmReport_2ByHostnameAndServer::class);
    }

//    /**
//     * @return ipmReport_2ByHostnameAndServer[] Returns an array of ipmReport_2ByHostnameAndServer objects
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

//    public function findOneBySomeField($value): ?ipmReport_2ByHostnameAndServer
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
