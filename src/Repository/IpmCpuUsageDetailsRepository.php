<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\IpmCpuUsageDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmCpuUsageDetails>
 *
 * @method IpmCpuUsageDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmCpuUsageDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmCpuUsageDetails[]    findAll()
 * @method IpmCpuUsageDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmCpuUsageDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmCpuUsageDetails::class);
    }
}
