<?php

namespace App\Repository;

use App\Entity\IpmPinbaTagInfoGroupServerServerNameHostname;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmPinbaTagInfoGroupServerServerNameHostname>
 *
 * @method IpmPinbaTagInfoGroupServerServerNameHostname|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmPinbaTagInfoGroupServerServerNameHostname|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmPinbaTagInfoGroupServerServerNameHostname[]    findAll()
 * @method IpmPinbaTagInfoGroupServerServerNameHostname[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmPinbaTagInfoGroupServerServerNameHostnameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmPinbaTagInfoGroupServerServerNameHostname::class);
    }

//    /**
//     * @return IpmPinbaTagInfoGroupServerServerNameHostname[] Returns an array of IpmPinbaTagInfoGroupServerServerNameHostname objects
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

//    public function findOneBySomeField($value): ?IpmPinbaTagInfoGroupServerServerNameHostname
//    {
//        return $this->createQueryBuilder('i')
//            ->andWhere('i.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
