<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\IpmStatusDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmStatusDetails>
 *
 * @method IpmStatusDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmStatusDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmStatusDetails[]    findAll()
 * @method IpmStatusDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmStatusDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmStatusDetails::class);
    }
}
