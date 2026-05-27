<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\IpmPinbaReportByHostnameAndServer_90_95_99;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmPinbaReportByHostnameAndServer_90_95_99>
 *
 * @method IpmPinbaReportByHostnameAndServer_90_95_99|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmPinbaReportByHostnameAndServer_90_95_99|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmPinbaReportByHostnameAndServer_90_95_99[]    findAll()
 * @method IpmPinbaReportByHostnameAndServer_90_95_99[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmPinbaReportByHostnameAndServer_90_95_99Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmPinbaReportByHostnameAndServer_90_95_99::class);
    }
}
