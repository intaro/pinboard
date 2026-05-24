<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\IpmReqTimeDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmReqTimeDetails>
 *
 * @method IpmReqTimeDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmReqTimeDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmReqTimeDetails[]    findAll()
 * @method IpmReqTimeDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmReqTimeDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmReqTimeDetails::class);
    }
}
