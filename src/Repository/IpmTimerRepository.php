<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\IpmTimer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmTimer>
 *
 * @method IpmTimer|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmTimer|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmTimer[]    findAll()
 * @method IpmTimer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmTimerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmTimer::class);
    }
}
