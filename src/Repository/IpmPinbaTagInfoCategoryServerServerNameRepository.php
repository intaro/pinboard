<?php

namespace App\Repository;

use App\Entity\IpmPinbaTagInfoCategoryServerServerName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmPinbaTagInfoCategoryServerServerName>
 *
 * @method IpmPinbaTagInfoCategoryServerServerName|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmPinbaTagInfoCategoryServerServerName|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmPinbaTagInfoCategoryServerServerName[]    findAll()
 * @method IpmPinbaTagInfoCategoryServerServerName[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmPinbaTagInfoCategoryServerServerNameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmPinbaTagInfoCategoryServerServerName::class);
    }

//    /**
//     * @return IpmPinbaTagInfoCategoryServerServerName[] Returns an array of IpmPinbaTagInfoCategoryServerServerName objects
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

//    public function findOneBySomeField($value): ?IpmPinbaTagInfoCategoryServerServerName
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
