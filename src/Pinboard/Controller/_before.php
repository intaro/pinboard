<?php

use Doctrine\DBAL\Cache\QueryCacheProfile;

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
    
    $maxReqCount = 0;
    foreach($list as $item) {
        if ($item['req_count'] > $maxReqCount) {
            $maxReqCount = $item['req_count'];
        }
    }
    
    foreach($list as $data) {
        if ($data['req_count'] > $maxReqCount / 2) {
            $data['label'] = 'important';
        }
        else {
            $data['label'] = 'inverse';
        }
        $result['servers'][$data['server_name']] = $data;
    }        
    
    $app['menu'] = $result;
});
