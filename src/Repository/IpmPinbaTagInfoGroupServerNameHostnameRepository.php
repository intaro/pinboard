<?php

namespace App\Repository;

use App\Entity\IpmPinbaTagInfoGroupServerNameHostname;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmPinbaTagInfoGroupServerNameHostname>
 *
 * @method IpmPinbaTagInfoGroupServerNameHostname|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmPinbaTagInfoGroupServerNameHostname|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmPinbaTagInfoGroupServerNameHostname[]    findAll()
 * @method IpmPinbaTagInfoGroupServerNameHostname[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmPinbaTagInfoGroupServerNameHostnameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmPinbaTagInfoGroupServerNameHostname::class);
    }

//    /**
//     * @return IpmPinbaTagInfoGroupServerNameHostname[] Returns an array of IpmPinbaTagInfoGroupServerNameHostname objects
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

//    public function findOneBySomeField($value): ?IpmPinbaTagInfoGroupServerNameHostname
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
