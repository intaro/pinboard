<?php

$app->before(function() use ($app) {
    $result = array();
    
    $sql = '
        SELECT
            server_name, req_count, count(created_at) cnt
        FROM
            ipm_report_by_server_name
        GROUP BY
            server_name
        HAVING
            cnt > 10
        ORDER BY
            server_name
    ';
    
    $list = $app['db']->fetchAll($sql);
    
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
