<?php

use Doctrine\DBAL\Cache\QueryCacheProfile;
use Pinboard\Utils\IDNaConvert;

$app->before(function() use ($app) {
    $result = array();

    $hosts = ".*";

    if (isset($app['params']['secure']['enable']) && $app['params']['secure']['enable']) {
        $user = $app['security']->getToken()->getUser();
        $hosts = isset($app['params']['secure']['users'][$user->getUsername()]['hosts'])
                    ? $app['params']['secure']['users'][$user->getUsername()]['hosts'] 
                    : ".*";
        if (trim($hosts) == "") {
            $hosts = ".*";
        }
    }

    $hostsWhere = '';
    $params = array();
    
    if ($hosts != '.*') {
        $params = array(
            'hosts' => $hosts,
        );
        $hostsWhere = 'WHERE server_name REGEXP :hosts';
    }    

    $sql = '
        SELECT
            server_name, req_count, count(created_at) cnt
        FROM
            ipm_report_by_server_name
        ' . $hostsWhere . '
        GROUP BY
            server_name
        HAVING
            cnt > 10
        ORDER BY
            server_name
    ';
    
    $stmt = $app['db']->executeQuery($sql, $params, array(), new QueryCacheProfile(5 * 60));
    $list = $stmt->fetchAll();
    $stmt->closeCursor();
    
    $idn = new IDNaConvert(array('idn_version' => 2008));
    
    foreach($list as $data) {
        if (stripos($data['server_name'], 'xn--') !== false) {
            $data['server_name'] = $idn->decode($data['server_name']);
        }
        
        $domainParts = explode('.', $data['server_name']);
        if (sizeof($domainParts) > 1) {
            $baseDomain = $domainParts[sizeof($domainParts) - 2] . '.' . $domainParts[sizeof($domainParts) - 1];
        }
        else {
            $baseDomain = $data['server_name'];
        }
        $result['servers'][$baseDomain][$data['server_name']] = $data;
    }        

    $app['menu'] = $result;
});
