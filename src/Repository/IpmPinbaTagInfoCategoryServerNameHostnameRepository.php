<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\IpmPinbaTagInfoCategoryServerNameHostname;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmPinbaTagInfoCategoryServerNameHostname>
 *
 * @method IpmPinbaTagInfoCategoryServerNameHostname|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmPinbaTagInfoCategoryServerNameHostname|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmPinbaTagInfoCategoryServerNameHostname[]    findAll()
 * @method IpmPinbaTagInfoCategoryServerNameHostname[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmPinbaTagInfoCategoryServerNameHostnameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmPinbaTagInfoCategoryServerNameHostname::class);
    }
}
