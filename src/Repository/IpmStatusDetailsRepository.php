<?php

namespace App\Repository;

use App\Entity\IpmStatusDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmStatusDetails>
 *
 * @method IpmStatusDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmStatusDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmStatusDetails[]    findAll()
 * @method IpmStatusDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmStatusDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmStatusDetails::class);
    }

//    /**
//     * @return IpmStatusDetails[] Returns an array of IpmStatusDetails objects
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

//    public function findOneBySomeField($value): ?IpmStatusDetails
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
