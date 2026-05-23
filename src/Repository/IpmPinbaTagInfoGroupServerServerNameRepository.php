<?php

namespace App\Repository;

use App\Entity\IpmPinbaTagInfoGroupServerServerName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmPinbaTagInfoGroupServerServerName>
 *
 * @method IpmPinbaTagInfoGroupServerServerName|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmPinbaTagInfoGroupServerServerName|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmPinbaTagInfoGroupServerServerName[]    findAll()
 * @method IpmPinbaTagInfoGroupServerServerName[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmPinbaTagInfoGroupServerServerNameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmPinbaTagInfoGroupServerServerName::class);
    }

//    /**
//     * @return IpmPinbaTagInfoGroupServerServerName[] Returns an array of IpmPinbaTagInfoGroupServerServerName objects
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

//    public function findOneBySomeField($value): ?IpmPinbaTagInfoGroupServerServerName
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
