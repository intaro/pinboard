<?php

namespace App\Repository;

use App\Entity\IpmPinbaTagInfoCategoryServerNameHostname;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmPinbaTagInfoCategoryServerNameHostname>
 *
 * @method IpmPinbaTagInfoCategoryServerNameHostname|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmPinbaTagInfoCategoryServerNameHostname|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmPinbaTagInfoCategoryServerNameHostname[]    findAll()
 * @method IpmPinbaTagInfoCategoryServerNameHostname[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmPinbaTagInfoCategoryServerNameHostnameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmPinbaTagInfoCategoryServerNameHostname::class);
    }

//    /**
//     * @return IpmPinbaTagInfoCategoryServerNameHostname[] Returns an array of IpmPinbaTagInfoCategoryServerNameHostname objects
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

//    public function findOneBySomeField($value): ?IpmPinbaTagInfoCategoryServerNameHostname
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
