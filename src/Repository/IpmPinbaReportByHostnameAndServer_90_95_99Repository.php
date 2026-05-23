<?php

namespace App\Repository;

use App\Entity\IpmPinbaReportByHostnameAndServer_90_95_99;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmPinbaReportByHostnameAndServer_90_95_99>
 *
 * @method IpmPinbaReportByHostnameAndServer_90_95_99|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmPinbaReportByHostnameAndServer_90_95_99|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmPinbaReportByHostnameAndServer_90_95_99[]    findAll()
 * @method IpmPinbaReportByHostnameAndServer_90_95_99[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmPinbaReportByHostnameAndServer_90_95_99Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmPinbaReportByHostnameAndServer_90_95_99::class);
    }

//    /**
//     * @return IpmPinbaReportByHostnameAndServer_90_95_99[] Returns an array of IpmPinbaReportByHostnameAndServer_90_95_99 objects
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

//    public function findOneBySomeField($value): ?IpmPinbaReportByHostnameAndServer_90_95_99
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
