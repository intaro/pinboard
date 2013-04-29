<?php

$index = $app['controllers_factory'];

$index->get('/', function() use ($app) {
    $result = array();

    $params = array(
        'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
    );
    
    $sql = '
        SELECT
            a.server_name, sum(a.req_count) as req_count, avg(a.req_per_sec) as req_per_sec, count(b.script_name) as error_count
        FROM
            ipm_report_by_hostname_and_server a
        LEFT JOIN
            ipm_status_details b 
        ON 
            a.server_name = b.server_name AND b.status >= 500
        WHERE
            a.created_at > :created_at
        GROUP BY
            a.server_name
    ';
    
    $result['servers'] = $app['db']->fetchAll($sql, $params);
    
    foreach($result['servers'] as &$item) {
        $item['req_per_sec'] = number_format($item['req_per_sec'], 3, ',', '');
    }
    
    return $app['twig']->render('index.html.twig', $result);
})->bind('index');

return $index;