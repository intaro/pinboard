<?php

use Pinboard\Utils\Utils;
use Symfony\Component\HttpFoundation\Request;

$ROW_PER_PAGE = 50;
$rowPerPage = isset($app['params']['pagination']['row_per_page']) ? $app['params']['pagination']['row_per_page'] : $ROW_PER_PAGE;
$rowPerPage = ($rowPerPage > 0) ? $rowPerPage : $ROW_PER_PAGE;

$server = $app['controllers_factory'];

function checkUserAccess($app, $serverName) {
    $hostsRegExp = ".*";
    if (isset($app['params']['secure']['enable'])) {
        if ($app['params']['secure']['enable'] == "true") {
            $user = $app['security']->getToken()->getUser();
            $hostsRegExp = isset($app['params']['secure']['users'][$user->getUsername()]['hosts'])
                        ? $app['params']['secure']['users'][$user->getUsername()]['hosts'] 
                        : ".*";
            if (trim($hostsRegExp) == "") {
                $hosts = ".*";
            }
        }
    }

    if (!preg_match("/" . $hostsRegExp . "/", $serverName)) {
        $app->abort(403, "Access denied");
    }
}

$server->get('/{serverName}/{hostName}', function($serverName, $hostName) use ($app) {
    checkUserAccess($app, $serverName);

    $result = array(
        'server_name' => $serverName,
        'hostname'    => $hostName,
        'title'       => $serverName,
    );
    
    $result['hosts']       = getHosts($app['db'], $serverName);    
    $result['statuses']    = getStatusesReview($app['db'], $serverName, $hostName);
    $result['req_per_sec'] = getRequestPerSecReview($app['db'], $serverName, $hostName);
    $result['req']         = getRequestReview($app['db'], $serverName, $hostName);

    return $app['twig']->render(
        'server.html.twig', 
        $result
    );
})
->value('hostName', 'all')
->bind('server');

function getHosts($conn, $serverName) {
    $sql = '
        SELECT
            DISTINCT hostname
        FROM
            ipm_report_by_hostname_and_server
        WHERE
            server_name = :server_name
        ORDER BY
            hostname
    ';

    $stmt = $conn->executeQuery($sql, array('server_name' => $serverName));
    $hosts = array();
    while ($data = $stmt->fetch()) {
      $hosts[] = $data['hostname'];
    }
    
    return $hosts;
}

function getStatusesReview($conn, $serverName, $hostName) {
    $params = array(
        'server_name' => $serverName,
        'created_at'  => date('Y-m-d H:i:s', strtotime('-1 day')),
    );
    $hostCondition = '';
    
    if ($hostName != 'all') {
        $params['hostname'] = $hostName;
        $hostCondition = 'AND hostname = :hostname';
    }
    
    $sql = '
        SELECT
            created_at, status, count(*) as cnt
        FROM
            ipm_status_details
        WHERE
            server_name = :server_name
            ' . $hostCondition . '
            AND created_at > :created_at
        GROUP BY
            created_at
        ORDER BY
            created_at
    ';
    
    $stmt = $conn->executeQuery($sql, $params);
    
    $statuses = array(
        'data'  => array(),
        'codes' => array(),
    );
    while ($data = $stmt->fetch()) {
        $t = strtotime($data['created_at']);
        $date = date('Y,', $t) . (date('n', $t) - 1) . date(',d,H,i', $t);
        
        $statuses['data'][$date][$data['status'] > 0 ? $data['status'] : 'none'] = $data['cnt'];
        if (!isset($statuses['codes'][$data['status']])) {
            //set color
            $statuses['codes'][$data['status']] = Utils::generateColor();
        }
    }          
    ksort($statuses['codes']);

    return $statuses;            
}

function getRequestPerSecReview($conn, $serverName, $hostName) {
    $params = array(
        'server_name' => $serverName,
        'created_at'  => date('Y-m-d H:i:s', strtotime('-1 day')),
    );
    $table = 'ipm_report_by_server_name';
    $hostCondition = '';
    
    if ($hostName != 'all') {
        $params['hostname'] = $hostName;
        $table = 'ipm_report_by_hostname_and_server';
        $hostCondition = 'AND hostname = :hostname';
    }
    
    $sql = '
        SELECT
            created_at, req_per_sec
        FROM
            ' . $table . '
        WHERE
            server_name = :server_name
            ' . $hostCondition . '
            AND created_at > :created_at
        ORDER BY
            created_at
    ';
    
    $data = $conn->fetchAll($sql, $params);
    
    foreach($data as &$item) {
        $t = strtotime($item['created_at']);
        $item['date'] = date('Y,', $t) . (date('n', $t) - 1) . date(',d,H,i', $t);
        $item['req_per_sec'] = number_format($item['req_per_sec'], 2, '.', '');
    }

    return $data;
}

function getRequestReview($conn, $serverName, $hostName) {
    $params = array(
        'server_name' => $serverName,
        'created_at'  => date('Y-m-d H:i:s', strtotime('-1 day')),
    );
    $hostCondition = '';
    $index = 'sn_c';
    
    if ($hostName != 'all') {
        $params['hostname'] = $hostName;
        $hostCondition = 'AND hostname = :hostname';
        $index = 'sn_h_c';
    }
    
    $sql = '
        SELECT
            created_at, 
            req_time_90, req_time_95, req_time_99, req_time_100,
            mem_peak_usage_90, mem_peak_usage_95, mem_peak_usage_99, mem_peak_usage_100
        FROM
            ipm_report_2_by_hostname_and_server
        USE INDEX
            (' . $index . ')
        WHERE
            server_name = :server_name
            ' . $hostCondition . '
            AND created_at > :created_at
        ORDER BY
            created_at
    ';
    
    $data = $conn->fetchAll($sql, $params);
    
    foreach($data as &$item) {
        $t = strtotime($item['created_at']);
        $item['date'] = date('Y,', $t) . (date('n', $t) - 1) . date(',d,H,i', $t);
        $item['req_time_90']  = number_format($item['req_time_90'] * 1000, 0, '.', '');
        $item['req_time_95']  = number_format($item['req_time_95'] * 1000, 0, '.', '');
        $item['req_time_99']  = number_format($item['req_time_99'] * 1000, 0, '.', '');
        $item['req_time_100'] = number_format($item['req_time_100'] * 1000, 0, '.', '');
        $item['mem_peak_usage_90']  = number_format($item['mem_peak_usage_90'], 0, '.', '');
        $item['mem_peak_usage_95']  = number_format($item['mem_peak_usage_95'], 0, '.', '');
        $item['mem_peak_usage_99']  = number_format($item['mem_peak_usage_99'], 0, '.', '');
        $item['mem_peak_usage_100'] = number_format($item['mem_peak_usage_100'], 0, '.', '');
    }

    return $data;
}


$server->get('/{serverName}/{hostName}/statuses/{pageNum}', function($serverName, $hostName, $pageNum) use ($app, $rowPerPage) {
    checkUserAccess($app, $serverName);

    $pageNum = str_replace('page', '', $pageNum);

    $result = array(
        'server_name' => $serverName,
        'hostname'    => $hostName,
        'title'       => 'Error pages / ' . $serverName,
        'pageNum'     => $pageNum,
    );
    
    $result['rowPerPage'] = $rowPerPage;

    $rowCount = getErrorPagesCount($app['db'], $serverName, $hostName);
    $result['rowCount'] = $rowCount;

    $pageCount = ceil($rowCount / $rowPerPage);
    $result['pageCount'] = $pageCount;

    if ($pageCount != 0) {
        if ($pageNum < 1 || $pageNum > $pageCount) {
            $app->abort(404, "Page $pageNum does not exist.");
        }
    }   

    $startPos = ($pageNum - 1) * $rowPerPage;
    $result['hosts']    = getHosts($app['db'], $serverName);    
    $result['statuses'] = getErrorPages($app['db'], $serverName, $hostName, $startPos, $rowPerPage);

    return $app['twig']->render(
        'statuses.html.twig', 
        $result
    );
})
->value('hostName', 'all')
->value('pageNum', 'page1')
->assert('pageNum', 'page\d+')
->bind('server_statuses');

function getErrorPagesCount($conn, $serverName, $hostName) {
    $params = array(
        'server_name' => $serverName,
        'created_at'  => date('Y-m-d H:i:s', strtotime('-1 day')),
    );
    $hostCondition = '';
    
    if ($hostName != 'all') {
        $params['hostname'] = $hostName;
        $hostCondition = 'AND hostname = :hostname';
    }

    $sql = '
        SELECT
            COUNT(*)
        FROM
            ipm_status_details
        WHERE
            server_name = :server_name
            ' . $hostCondition . '
            AND created_at > :created_at
    ';

    $data = $conn->fetchAll($sql, $params);

    return (int)$data[0]['COUNT(*)'];
}

function getErrorPages($conn, $serverName, $hostName, $startPos, $rowCount) {
    $params = array(
        'server_name' => $serverName,
        'created_at'  => date('Y-m-d H:i:s', strtotime('-1 day')),
    );
    $hostCondition = '';
    
    if ($hostName != 'all') {
        $params['hostname'] = $hostName;
        $hostCondition = 'AND hostname = :hostname';
    }

    $sql = '
        SELECT
            DISTINCT server_name, hostname, script_name, status, created_at
        FROM
            ipm_status_details
        WHERE
            server_name = :server_name
            ' . $hostCondition . '
            AND created_at > :created_at
        ORDER BY
            created_at DESC
        LIMIT
            ' . $startPos . ', ' . $rowCount . '
    ';

    $data = $conn->fetchAll($sql, $params);
    
    return $data;
}

$server->get('/{serverName}/{hostName}/req-time/{pageNum}', function($serverName, $hostName, $pageNum) use ($app, $rowPerPage) {
    checkUserAccess($app, $serverName);
    
    $pageNum = str_replace('page', '', $pageNum);
    
    $result = array(
        'server_name' => $serverName,
        'hostname'    => $hostName,
        'title'       => 'Request time / ' . $serverName,
        'pageNum'     => $pageNum,
    );

    $result['rowPerPage'] = $rowPerPage;

    $rowCount = getSlowPagesCount($app['db'], $serverName, $hostName);
    $result['rowCount'] = $rowCount;

    $pageCount = ceil($rowCount / $rowPerPage);
    $result['pageCount'] = $pageCount;
    if ($pageCount != 0) {
        if ($pageNum < 1 || $pageNum > $pageCount) {
            $app->abort(404, "Page $pageNum does not exist.");
        }
    }
    $startPos = ($pageNum - 1) * $rowPerPage;

    $result['hosts'] = getHosts($app['db'], $serverName);    
    $result['pages'] = getSlowPages($app['db'], $serverName, $hostName, $startPos, $rowPerPage);

    return $app['twig']->render(
        'req_time.html.twig', 
        $result
    );
})
->value('hostName', 'all')
->value('pageNum', 'page1')
->assert('pageNum', 'page\d+')
->bind('server_req_time');

function getSlowPagesCount($conn, $serverName, $hostName) {
    $params = array(
        'server_name' => $serverName,
        'created_at'  => date('Y-m-d H:i:s', strtotime('-1 day')),
    );
    $hostCondition = '';
    
    if ($hostName != 'all') {
        $params['hostname'] = $hostName;
        $hostCondition = 'AND hostname = :hostname';
    }

    $sql = '
        SELECT
            COUNT(*)
        FROM
            ipm_req_time_details
        WHERE
            server_name = :server_name
            ' . $hostCondition . '
            AND created_at > :created_at
    ';

    $data = $conn->fetchAll($sql, $params);

    return (int)$data[0]['COUNT(*)'];
}

function getSlowPages($conn, $serverName, $hostName, $startPos, $rowCount) {
    $params = array(
        'server_name' => $serverName,
        'created_at'  => date('Y-m-d H:i:s', strtotime('-1 day')),
    );
    $hostCondition = '';
    
    if ($hostName != 'all') {
        $params['hostname'] = $hostName;
        $hostCondition = 'AND hostname = :hostname';
    }

    $sql = '
        SELECT
            DISTINCT server_name, hostname, script_name, req_time, created_at
        FROM
            ipm_req_time_details
        WHERE
            server_name = :server_name
            ' . $hostCondition . '
            AND created_at > :created_at
        ORDER BY
            created_at DESC, req_time DESC
        LIMIT
            ' . $startPos . ', ' . $rowCount . '
    ';

    $data = $conn->fetchAll($sql, $params);
    
    foreach($data as &$item) {
        $item['req_time']  = number_format($item['req_time'] * 1000, 0, '.', ',');
    }
    
    return $data;
}

$server->get('/{serverName}/{hostName}/mem-usage/{pageNum}', function($serverName, $hostName, $pageNum) use ($app, $rowPerPage) {
    checkUserAccess($app, $serverName);
    
    $pageNum = str_replace('page', '', $pageNum);
    
    $result = array(
        'server_name' => $serverName,
        'hostname'    => $hostName,
        'title'       => 'Memory peak usage / ' . $serverName,
        'pageNum'     => $pageNum,
    );
    
    $result['rowPerPage'] = $rowPerPage;

    $rowCount = getHeavyPagesCount($app['db'], $serverName, $hostName);
    $result['rowCount'] = $rowCount;

    $pageCount = ceil($rowCount / $rowPerPage);
    $result['pageCount'] = $pageCount;
    if ($pageCount != 0) {
        if ($pageNum < 1 || $pageNum > $pageCount) {
            $app->abort(404, "Page $pageNum does not exist.");
        }
    }
    $startPos = ($pageNum - 1) * $rowPerPage;

    $result['hosts'] = getHosts($app['db'], $serverName);    
    $result['pages'] = getHeavyPages($app['db'], $serverName, $hostName, $startPos, $rowPerPage);

    return $app['twig']->render(
        'mem_usage.html.twig', 
        $result
    );
})
->value('hostName', 'all')
->value('pageNum', 'page1')
->assert('pageNum', 'page\d+')
->bind('server_mem_usage');

function getHeavyPagesCount($conn, $serverName, $hostName){
    $params = array(
        'server_name' => $serverName,
        'created_at'  => date('Y-m-d H:i:s', strtotime('-1 day')),
    );
    $hostCondition = '';
    
    if ($hostName != 'all') {
        $params['hostname'] = $hostName;
        $hostCondition = 'AND hostname = :hostname';
    }

    $sql = '
        SELECT
            COUNT(*)
        FROM
            ipm_mem_peak_usage_details
        WHERE
            server_name = :server_name
            ' . $hostCondition . '
            AND created_at > :created_at
    ';

    $data = $conn->fetchAll($sql, $params);

    return (int)$data[0]['COUNT(*)'];
}

function getHeavyPages($conn, $serverName, $hostName, $startPos, $rowCount) {
    $params = array(
        'server_name' => $serverName,
        'created_at'  => date('Y-m-d H:i:s', strtotime('-1 day')),
    );
    $hostCondition = '';
    
    if ($hostName != 'all') {
        $params['hostname'] = $hostName;
        $hostCondition = 'AND hostname = :hostname';
    }

    $sql = '
        SELECT
            DISTINCT server_name, hostname, script_name, mem_peak_usage, created_at
        FROM
            ipm_mem_peak_usage_details
        WHERE
            server_name = :server_name
            ' . $hostCondition . '
            AND created_at > :created_at
        ORDER BY
            created_at DESC, mem_peak_usage DESC
        LIMIT
            ' . $startPos . ', ' . $rowCount . '
    ';

    $data = $conn->fetchAll($sql, $params);
    
    foreach($data as &$item) {
        $item['mem_peak_usage']  = number_format($item['mem_peak_usage'], 0, '.', ',');
    }
    
    return $data;
}

$server->get('/{serverName}/{hostName}/live', function(Request $request, $serverName, $hostName) use ($app) {
    checkUserAccess($app, $serverName);
    
    if ($request->isXmlHttpRequest()) {
        $result = array(
            'pages' => getLivePages($app['db'], $serverName, $hostName, $request->get('last_id')),
        );
        
        return $app->json($result);
    }

    $result = array(
        'server_name' => $serverName,
        'hostname'    => $hostName,
        'title'       => 'Live / ' . $serverName,
        'limit'       => 100,
    );
    
    $result['hosts'] = getHosts($app['db'], $serverName);    
    $result['pages'] = getLivePages($app['db'], $serverName, $hostName, null, $result['limit']);
    $result['last_id'] = sizeof($result['pages']) ? $result['pages'][0]['id'] : 0;
        
    return $app['twig']->render(
        'live.html.twig', 
        $result
    );
})
->value('hostName', 'all')
->bind('server_live');


function getLivePages($conn, $serverName, $hostName, $lastId = null, $limit = 50) {
    $params = array(
        'server_name' => $serverName,
    );
    $hostCondition = '';
    $idCondition   = '';
    
    if ($hostName != 'all') {
        $params['hostname'] = $hostName;
        $hostCondition = 'AND hostname = :hostname';
    }
    if ($lastId > 0) {
        $params['last_id'] = $lastId;
        $idCondition = 'AND id > :last_id';
    }

    $sql = '
        SELECT
            id, server_name, hostname, script_name, req_time, mem_peak_usage
        FROM
            request
        WHERE
            server_name = :server_name
            ' . $hostCondition . '
            ' . $idCondition . '
        ORDER BY
            id DESC
        LIMIT
            ' . $limit . '
    ';

    $data = $conn->fetchAll($sql, $params);
    
    foreach($data as &$item) {
        $item['req_time']        = number_format($item['req_time'] * 1000, 0, '.', ',');
        $item['mem_peak_usage']  = $item['mem_peak_usage'];
    }
    
    return $data;
}

$server->get('/{serverName}/{hostName}/overview.json', function(Request $request, $serverName, $hostName) use ($app) {
    if (!$request->get('apiKey'))
    {
        $responce = new Symfony\Component\HttpFoundation\JsonResponse(array(
            'errorMsg' => 'Need "apiKey" value',
            'success' => 'false',
        ));
        $responce->setStatusCode(400);
        return $responce;
    }
    
    $apiKey = $request->get('apiKey');
 
    if (!array_key_exists($apiKey, $app['params']['api_keys'])) {
        $responce = new Symfony\Component\HttpFoundation\JsonResponse(array(
            'errorMsg' => 'Wrong "apiKey" value',
            'success' => 'false',
        ));
        $responce->setStatusCode(403);
        return $responce;
    }
    
    $hostsRegExp = '.*';
    if ($app['params']['api_keys'][$apiKey] != NULL) {
        $hostsRegExp = $app['params']['api_keys'][$apiKey];
    }
    
    if (!preg_match("/" . $hostsRegExp . "/", $serverName)) {
        $responce = new Symfony\Component\HttpFoundation\JsonResponse(array(
            'errorMsg' => 'Access denied',
            'success' => 'false',
        ));
        $responce->setStatusCode(403);
        return $responce;
    }
        
    $result = array();

    $req = getRequestReview($app['db'], $serverName, $hostName);
    $result['req_time'] = array();
    $result['mem_peak_usage'] = array();
    foreach ($req as $key => $value) {
        $result['req_time'][$key] = array(
            'created_at' => $value['created_at'],
            'date' => $value['date'],
            'req_time_90' => $value['req_time_90'],
            'req_time_95' => $value['req_time_95'],
            'req_time_99' => $value['req_time_99'],
            'req_time_100' => $value['req_time_100'],
        );
        $result['mem_peak_usage'][$key] = array(
            'created_at' => $value['created_at'],
            'date' => $value['date'],
            'mem_peak_usage_90' => $value['mem_peak_usage_90'],
            'mem_peak_usage_95' => $value['mem_peak_usage_95'],
            'mem_peak_usage_99' => $value['mem_peak_usage_99'],
            'mem_peak_usage_100' => $value['mem_peak_usage_100'],
        );
    }
    $result['req_per_sec'] = getRequestPerSecReview($app['db'], $serverName, $hostName);
    $result['statuses']    = getStatusesReview($app['db'], $serverName, $hostName);
    
    $result['success'] = 'true';
    $responce = new Symfony\Component\HttpFoundation\JsonResponse($result);
    $responce->setStatusCode(200);
    return $responce;
})
->value('hostName', 'all')
->bind('api_overview');

return $server;