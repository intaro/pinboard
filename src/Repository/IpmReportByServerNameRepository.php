<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\IpmReportByServerName;
use App\Utils\DateTimeUtils;
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

    /**
     * @return list<array{server_name: string, req_count: int, req_per_sec: float, error_count: int}>
     */
    public function findAllServers(string $hostsRegexp = '.*'): array
    {
        $params = ['created_at' => DateTimeUtils::storageDateTimeAgo('1 day', 'Y-m-d H:00:00')];
        $hostsWhere = '';

        if ($hostsRegexp !== '.*') {
            $hostsWhere = 'AND a.server_name REGEXP :hosts_regexp';
            $params['hosts_regexp'] = $hostsRegexp;
        }

        $sql = "
            SELECT a.server_name,
                   sum(a.req_count)    as req_count,
                   avg(a.req_per_sec)  as req_per_sec,
                   (
                       SELECT count(*)
                       FROM ipm_status_details b
                       WHERE b.server_name = a.server_name
                         AND b.created_at > :created_at
                   ) as error_count
            FROM ipm_report_by_server_name a
            WHERE a.created_at > :created_at
              $hostsWhere
            GROUP BY a.server_name
            ORDER BY a.server_name
        ";

        $rows = $this->getEntityManager()->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        return array_map(static fn (array $row): array => [
            'server_name' => is_string($row['server_name']) ? $row['server_name'] : '',
            'req_count'   => is_numeric($row['req_count']) ? (int) $row['req_count'] : 0,
            'req_per_sec' => is_numeric($row['req_per_sec']) ? (float) $row['req_per_sec'] : 0.0,
            'error_count' => is_numeric($row['error_count']) ? (int) $row['error_count'] : 0,
        ], $rows);
    }
}
