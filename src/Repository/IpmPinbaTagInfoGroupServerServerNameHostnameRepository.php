<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\IpmPinbaTagInfoGroupServerServerNameHostname;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmPinbaTagInfoGroupServerServerNameHostname>
 *
 * @method IpmPinbaTagInfoGroupServerServerNameHostname|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmPinbaTagInfoGroupServerServerNameHostname|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmPinbaTagInfoGroupServerServerNameHostname[]    findAll()
 * @method IpmPinbaTagInfoGroupServerServerNameHostname[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmPinbaTagInfoGroupServerServerNameHostnameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmPinbaTagInfoGroupServerServerNameHostname::class);
    }
}
