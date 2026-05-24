<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\IpmTagInfo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmTagInfo>
 *
 * @method IpmTagInfo|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmTagInfo|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmTagInfo[]    findAll()
 * @method IpmTagInfo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmTagInfoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmTagInfo::class);
    }
}
