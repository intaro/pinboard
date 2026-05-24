<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\IpmMemPeakUsageDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmMemPeakUsageDetails>
 *
 * @method IpmMemPeakUsageDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmMemPeakUsageDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmMemPeakUsageDetails[]    findAll()
 * @method IpmMemPeakUsageDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmMemPeakUsageDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmMemPeakUsageDetails::class);
    }
}
