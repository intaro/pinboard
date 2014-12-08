<?php

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Pinboard\Utils\IDNaConvert;
use Pinboard\Utils\Utils;

$app->before(function() use ($app) {
    $result = array(
        'servers' => array(),
    );

    $params = array(
        'created_at' => date('Y-m-d H:00:00', strtotime('-1 day')),
    );

    $hostsRegexp = Utils::getUserAccessHostsRegexp($app);
    $hostsWhere = '';

    if ($hostsRegexp != '.*') {
        $hostsRegexp = is_array($hostsRegexp) ? $hostsRegexp : array($hostsRegexp);
        $hostsWhere = " AND (server_name REGEXP '" . implode("' OR server_name REGEXP '", $hostsRegexp) . "')";
    }

    $sql = '
        SELECT
            server_name, count(created_at) cnt
        FROM
            ipm_report_by_server_name
        WHERE
            created_at >= :created_at AND
            server_name IS NOT NULL AND server_name != \'\'
            ' . $hostsWhere . '
        GROUP BY
            server_name
        HAVING
            cnt > 10
        ORDER BY
            server_name
    ';

    $stmt = $app['db']->executeCacheQuery($sql, $params, array(), new QueryCacheProfile(60 * 60));
    $list = $stmt->fetchAll();
    $stmt->closeCursor();

    $idn = new IDNaConvert(array('idn_version' => 2008));

    $ips = array();
    foreach($list as $data) {
        if (stripos($data['server_name'], 'xn--') !== false) {
            $data['server_name'] = $idn->decode($data['server_name']);
        }

        if (preg_match('/\d+\.\d+\.\d+\.\d+/', $data['server_name'])) {
            $ips[$data['server_name']] = $data;
        }
        else {
            $domainParts = explode('.', $data['server_name']);
            if (sizeof($domainParts) > 1) {
                $baseDomain = $domainParts[sizeof($domainParts) - 2] . '.' . $domainParts[sizeof($domainParts) - 1];
            }
            else {
                $baseDomain = $data['server_name'];
            }
            $result['servers'][$baseDomain][$data['server_name']] = $data;
        }
    }

    ksort($result['servers']);
    if (sizeof($ips)) {
        $result['servers']['IPs'] = $ips;
    }

    $app['menu'] = $result;
});
