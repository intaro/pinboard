<?php

namespace App\Repository;

use App\Entity\IpmPinbaTagInfoCategoryServerName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmPinbaTagInfoCategoryServerName>
 *
 * @method IpmPinbaTagInfoCategoryServerName|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmPinbaTagInfoCategoryServerName|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmPinbaTagInfoCategoryServerName[]    findAll()
 * @method IpmPinbaTagInfoCategoryServerName[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmPinbaTagInfoCategoryServerNameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmPinbaTagInfoCategoryServerName::class);
    }

//    /**
//     * @return IpmPinbaTagInfoCategoryServerName[] Returns an array of IpmPinbaTagInfoCategoryServerName objects
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

//    public function findOneBySomeField($value): ?IpmPinbaTagInfoCategoryServerName
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
