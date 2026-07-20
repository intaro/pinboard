<?php

declare(strict_types=1);

namespace App\Controller;

use Algo26\IdnaConvert\ToUnicode;
use App\Utils\DateTimeUtils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BeforeController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    /** @return array<string, mixed> */
    public function actionBefore(string $hostsRegexp = '.*'): array
    {
        $result = [
            'servers' => []
        ];

        $params = [
            'created_at' => DateTimeUtils::storageDateTimeAgo('1 day', 'Y-m-d H:00:00')
        ];

        $hostsWhere = '';

        if ($hostsRegexp !== '.*') {
            $hostsWhere = 'AND server_name REGEXP :hosts_regexp';
            $params['hosts_regexp'] = $hostsRegexp;
        }

        $sql = "
            SELECT
                server_name, count(created_at) cnt
            FROM
                ipm_report_by_server_name
            WHERE
                created_at >= :created_at AND
                server_name IS NOT NULL AND server_name != ''
                $hostsWhere
            GROUP BY
                server_name
            HAVING
                cnt > 10
            ORDER BY
                server_name
        ";

        $list = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        $idn = new ToUnicode();

        $ips = [];
        foreach ($list as $data) {
            $serverName = $data['server_name'];
            if (!is_string($serverName) || $serverName === '') {
                continue;
            }

            if (stripos($serverName, 'xn--') !== false) {
                $serverName = $idn->convertUrl($serverName);
            }

            if (preg_match('/\d+\.\d+\.\d+\.\d+/', $serverName)) {
                $ips[$serverName] = $data;
            } else {
                $domainParts = explode('.', $serverName);
                if (count($domainParts) > 1) {
                    $baseDomain = $domainParts[count($domainParts) - 2] . '.' . $domainParts[count($domainParts) - 1];
                } else {
                    $baseDomain = $serverName;
                }
                $result['servers'][$baseDomain][$serverName] = $data;
            }
        }

        ksort($result['servers']);
        if (count($ips)) {
            $result['servers']['IPs'] = $ips;
        }

        return $result;
    }
}
