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

    public function findAllServers()
    {

        return $this->createQueryBuilder('a')
//            ->select("
//                a.server_name,
//                sum(a.req_count) as req_count,
//                avg(a.req_per_sec) as req_per_sec,
//                (
//                    SELECT
//                        count(b.server_name)
//                    FROM
//                        ipm_status_details b
//
//                )
//                as error_count")
            ->select("
                a.server_name,
                sum(a.req_count) as req_count,
                avg(a.req_per_sec) as req_per_sec,
                0 as error_count")
            ->andWhere('a.created_at > :created_at')
            ->setParameter('created_at', date('Y-m-d H:00:00', strtotime('-1 day')))
            ->groupBy('a.server_name')
            ->getQuery()
            ->getResult();

//        $hostsRegexp = Utils::getUserAccessHostsRegexp($app);
//        if ($hostsRegexp !== '.*') {
//            $hostsRegexp = is_array($hostsRegexp) ? $hostsRegexp : [$hostsRegexp];
//            $hostsWhere = " AND (a.server_name REGEXP '" . implode("' OR a.server_name REGEXP '", $hostsRegexp) . "')";
//        }
//
//        $sql = "
//            SELECT
//                a.server_name,
//                sum(a.req_count) as req_count,
//                avg(a.req_per_sec) as req_per_sec,
//                (
//                    SELECT
//                        count(*)
//                    FROM
//                        ipm_status_details b
//                    WHERE
//                        a.server_name = b.server_name AND b.created_at > :created_at
//                )
//                as error_count
//            FROM
//                ipm_report_by_hostname_and_server a
//            WHERE
//                a.created_at > :created_at
//                $hostsWhere
//            GROUP BY
//                a.server_name
//        ";
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
