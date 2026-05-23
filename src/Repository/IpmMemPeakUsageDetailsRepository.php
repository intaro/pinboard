<?php

namespace App\Repository;

use App\Entity\IpmMemPeakUsageDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmMemPeakUsageDetails>
 *
 * @method IpmMemPeakUsageDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmMemPeakUsageDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmMemPeakUsageDetails[]    findAll()
 * @method IpmMemPeakUsageDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmMemPeakUsageDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmMemPeakUsageDetails::class);
    }

//    /**
//     * @return IpmMemPeakUsageDetails[] Returns an array of IpmMemPeakUsageDetails objects
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

//    public function findOneBySomeField($value): ?IpmMemPeakUsageDetails
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
