<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\IpmReportByHostname;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmReportByHostname>
 *
 * @method IpmReportByHostname|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmReportByHostname|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmReportByHostname[]    findAll()
 * @method IpmReportByHostname[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmReportByHostnameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmReportByHostname::class);
    }
}
