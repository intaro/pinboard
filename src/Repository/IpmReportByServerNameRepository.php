<?php

namespace App\Repository;

use App\Entity\IpmReportByServerName;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<IpmReportByServerName>
 *
 * @method IpmReportByServerName|null find($id, $lockMode = null, $lockVersion = null)
 * @method IpmReportByServerName|null findOneBy(array $criteria, array $orderBy = null)
 * @method IpmReportByServerName[]    findAll()
 * @method IpmReportByServerName[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IpmReportByServerNameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, IpmReportByServerName::class);
    }

    public function findAllServers(string $hostsRegexp = '.*'): array
    {
        $params = ['created_at' => date('Y-m-d H:00:00', strtotime('-1 day'))];
        $hostsWhere = '';

        if ($hostsRegexp !== '.*') {
            $hostsWhere = 'AND server_name REGEXP :hosts_regexp';
            $params['hosts_regexp'] = $hostsRegexp;
        }

        $sql = "
            SELECT server_name,
                   sum(req_count)    as req_count,
                   avg(req_per_sec)  as req_per_sec,
                   0                 as error_count
            FROM ipm_report_by_server_name
            WHERE created_at > :created_at
              $hostsWhere
            GROUP BY server_name
            ORDER BY server_name
        ";

        return $this->getEntityManager()->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();
    }

//    /**
//     * @return Server[] Returns an array of Server objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Server
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
