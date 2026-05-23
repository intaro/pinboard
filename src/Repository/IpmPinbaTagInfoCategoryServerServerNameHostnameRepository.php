<?php

namespace App\Repository;

use App\Entity\IpmPinbaTagInfoCategoryServerServerNameHostname;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmPinbaTagInfoCategoryServerServerNameHostname>
 *
 * @method IpmPinbaTagInfoCategoryServerServerNameHostname|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmPinbaTagInfoCategoryServerServerNameHostname|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmPinbaTagInfoCategoryServerServerNameHostname[]    findAll()
 * @method IpmPinbaTagInfoCategoryServerServerNameHostname[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmPinbaTagInfoCategoryServerServerNameHostnameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmPinbaTagInfoCategoryServerServerNameHostname::class);
    }

//    /**
//     * @return IpmPinbaTagInfoCategoryServerServerNameHostname[] Returns an array of IpmPinbaTagInfoCategoryServerServerNameHostname objects
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

//    public function findOneBySomeField($value): ?IpmPinbaTagInfoCategoryServerServerNameHostname
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
