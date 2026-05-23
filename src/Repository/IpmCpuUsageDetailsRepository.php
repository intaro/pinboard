<?php

namespace App\Repository;

use App\Entity\IpmCpuUsageDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmCpuUsageDetails>
 *
 * @method IpmCpuUsageDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmCpuUsageDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmCpuUsageDetails[]    findAll()
 * @method IpmCpuUsageDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmCpuUsageDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmCpuUsageDetails::class);
    }

//    /**
//     * @return IpmCpuUsageDetails[] Returns an array of IpmCpuUsageDetails objects
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

//    public function findOneBySomeField($value): ?IpmCpuUsageDetails
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
