<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\IpmReportByHostnameAndServer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmReportByHostnameAndServer>
 *
 * @method IpmReportByHostnameAndServer|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmReportByHostnameAndServer|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmReportByHostnameAndServer[]    findAll()
 * @method IpmReportByHostnameAndServer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmReportByHostnameAndServerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmReportByHostnameAndServer::class);
    }
}
