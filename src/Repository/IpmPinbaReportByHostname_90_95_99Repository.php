<?php

namespace App\Repository;

use App\Entity\IpmPinbaReportByHostname_90_95_99;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmPinbaReportByHostname_90_95_99>
 *
 * @method IpmPinbaReportByHostname_90_95_99|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmPinbaReportByHostname_90_95_99|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmPinbaReportByHostname_90_95_99[]    findAll()
 * @method IpmPinbaReportByHostname_90_95_99[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmPinbaReportByHostname_90_95_99Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmPinbaReportByHostname_90_95_99::class);
    }

//    /**
//     * @return IpmPinbaReportByHostname_90_95_99[] Returns an array of IpmPinbaReportByHostname_90_95_99 objects
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

//    public function findOneBySomeField($value): ?IpmPinbaReportByHostname_90_95_99
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
