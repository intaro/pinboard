<?php

namespace App\Repository;

use App\Entity\IpmPinbaTagInfoGroupServerName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmPinbaTagInfoGroupServerName>
 *
 * @method IpmPinbaTagInfoGroupServerName|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmPinbaTagInfoGroupServerName|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmPinbaTagInfoGroupServerName[]    findAll()
 * @method IpmPinbaTagInfoGroupServerName[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmPinbaTagInfoGroupServerNameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmPinbaTagInfoGroupServerName::class);
    }

//    /**
//     * @return IpmPinbaTagInfoGroupServerName[] Returns an array of IpmPinbaTagInfoGroupServerName objects
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

//    public function findOneBySomeField($value): ?IpmPinbaTagInfoGroupServerName
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
