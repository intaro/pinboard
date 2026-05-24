<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\IpmPinbaReportByHostname_90_95_99;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmPinbaReportByHostname_90_95_99>
 *
 * @method IpmPinbaReportByHostname_90_95_99|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmPinbaReportByHostname_90_95_99|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmPinbaReportByHostname_90_95_99[]    findAll()
 * @method IpmPinbaReportByHostname_90_95_99[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmPinbaReportByHostname_90_95_99Repository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmPinbaReportByHostname_90_95_99::class);
    }
}
