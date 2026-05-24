<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ipmReport_2ByHostnameAndServer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ipmReport_2ByHostnameAndServer>
 *
 * @method ipmReport_2ByHostnameAndServer|null find($id, $lockMode = null, $lockVersion = null)
 * @method ipmReport_2ByHostnameAndServer|null findOneBy(array $criteria, array $orderBy = null)
 * @method ipmReport_2ByHostnameAndServer[]    findAll()
 * @method ipmReport_2ByHostnameAndServer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ipmReport_2ByHostnameAndServerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ipmReport_2ByHostnameAndServer::class);
    }
}
