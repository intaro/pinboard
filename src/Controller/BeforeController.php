<?php

declare(strict_types=1);

namespace App\Controller;

use Algo26\IdnaConvert\ToUnicode;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class BeforeController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    // Не в том месте
    //    #[Route('/before', name: 'before')]
    //    public function actionBefore(): Response
    public function actionBefore(string $hostsRegexp = '.*'): array
    {
        $result = [
            'servers' => []
        ];

        $params = [
            'created_at' => date('Y-m-d H:00:00', strtotime('-1 day'))
        ];

        $hostsWhere = '';

        if ($hostsRegexp !== '.*') {
            $hostsWhere = 'AND server_name REGEXP :hosts_regexp';
            $params['hosts_regexp'] = $hostsRegexp;
        }

        //        $sql = "
        //            SELECT
        //                server_name, count(created_at) cnt
        //            FROM
        //                ipm_report_by_server_name
        //            WHERE
        //                created_at >= :created_at AND
        //                server_name IS NOT NULL AND server_name != ''
        //                $hostsWhere
        //            GROUP BY
        //                server_name
        //            HAVING
        //                cnt > 10
        //            ORDER BY
        //                server_name
        //        ";

        // Для теста убрал HAVING, т.к. нет такой большой выборки по данным
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
            ORDER BY
                server_name
        ";

        // Возможно надо сделать какое-то кеширование, чтобы не сильно долбить базу
        $list = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        $idn = new ToUnicode();

        $ips = [];
        foreach ($list as $data) {
            if (stripos($data['server_name'], 'xn--') !== false) {
                $data['server_name'] = $idn->convertUrl($data['server_name']);
            }

            if (preg_match('/\d+\.\d+\.\d+\.\d+/', $data['server_name'])) {
                $ips[$data['server_name']] = $data;
            } else {
                $domainParts = explode('.', $data['server_name']);
                if (count($domainParts) > 1) {
                    $baseDomain = $domainParts[count($domainParts) - 2] . '.' . $domainParts[count($domainParts) - 1];
                } else {
                    $baseDomain = $data['server_name'];
                }
                $result['servers'][$baseDomain][$data['server_name']] = $data;
            }
        }

        ksort($result['servers']);
        if (count($ips)) {
            $result['servers']['IPs'] = $ips;
        }

        //        $app['menu'] = $result;
        //        Надо поправить, т.к. это заглушка
        return $result;
    }
}
