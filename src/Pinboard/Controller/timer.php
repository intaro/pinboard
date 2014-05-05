<?php

use Pinboard\Utils\Utils;
use Symfony\Component\HttpFoundation\Request;

$server = $app['controllers_factory'];
$requestTypes = array(
    'live' => 'request',
);

$server->get('/{type}/{requestId}/{grouping}', function($type, $requestId, $grouping) use ($app, $requestTypes) {
    if (!isset($requestTypes[$type])) {
        $app->abort(404, "Type $type not allowed. Allowed types: " . implode(', ', $requestTypes));
    }

    $request = getRequestById($app['db'], $requestTypes[$type], $requestId);
    if (!$request) {
        $app->abort(404, "Request #$requestId not found in table {$requestTypes[$type]}.");
    }

    Utils::checkUserAccess($app, $request['server_name']);

    $request['script_name'] = urldecode($request['script_name']);
    $request = Utils::parseRequestTags($request);

    if ($type == 'live') {
        $request['timers'] = getTimers($app['db'], $requestId);
    }

    $groupingTags = findGroupingTags($request['timers']);

    $grouping = preg_replace('/^grouping\-/', '', $grouping);
    if ($grouping == 'none' || empty($grouping)) {
        if (sizeof($groupingTags)) {
            if (in_array('category', $groupingTags)) {
                $grouping = 'category';
            }
            else {
                $grouping = $groupingTags[0];
            }
        }
        else {
            $grouping = null;
        }
    }

    if ($grouping) {
        $request['timers'] = groupTimers($request['timers'], $grouping);
    }
    else {
    }

    $request = formatRequestTimes($request);

    $result = array(
        'request' => $request,
        'title' => "Timers for $type request #$requestId",
        'grouping_tags' => $groupingTags,
        'grouping' => $grouping,
        'type' => $type,
        'request_id' => $requestId,
    );

    return $app['twig']->render(
        'timer.html.twig',
        $result
    );
})
->value('type', 'live')
->value('grouping', 'grouping-none')
->assert('requestId', '\d+')
->bind('timers_show');

function getRequestById($conn, $table, $id) {
    $params = array(
        'id' => $id,
    );

    $sql = "
        SELECT
            *
        FROM
            $table
        WHERE
            id = :id
    ";

    $data = $conn->fetchAll($sql, $params);

    if (sizeof($data)) {
        return $data[0];
    }

    return null;
}

function getTimers($conn, $id) {
    $params = array(
        'id' => $id,
    );

    $sql = "
        SELECT
            id, request_id, hit_count, value
        FROM
            timer
        WHERE
            request_id = :id
    ";

    $data = $conn->fetchAll($sql, $params);

    if (!sizeof($data)) {
        return array();
    }

    $timers = array();
    $ids = array();
    foreach ($data as $timer) {
        $timer['tags'] = array();
        $ids[] = $timer['id'];
        $timers[$timer['id']] = $timer;
    }
    unset($data);

    $sql = "
        SELECT
            tag.name as name, timertag.value as value, timertag.timer_id as timer_id
        FROM
            timertag
        JOIN
            tag ON timertag.tag_id = tag.id
        WHERE
            timertag.timer_id IN (?)
    ";

    $tags = $conn->executeQuery($sql, array($ids), array(\Doctrine\DBAL\Connection::PARAM_INT_ARRAY));

    foreach ($tags as $t) {
        $timers[$t['timer_id']]['tags'][$t['name']] = $t['value'];
    }

    return $timers;
}

// search tags which exist in all timers
function findGroupingTags($timers) {
    if (!sizeof($timers)) {
        return array();
    }

    $f = current($timers);
    $tags = array_keys($f['tags']);

    foreach ($timers as $timer) {
        foreach ($tags as $index => $tag) {
            if (!isset($timer['tags'][$tag])) {
                unset($tags[$index]);
            }
        }
    }

    return $tags;
}

function groupTimers($timers, $groupingTag) {
    $data = array();
    foreach ($timers as $timer) {
        $v = $timer['tags'][$groupingTag];
        if (!isset($data[$v])) {
            $data[$v] = array(
                'value' => 0,
                'hit_count' => 0,
            );
        }
        unset($timer['tags'][$groupingTag]);
        $data[$v]['value'] += $timer['value'];
        $data[$v]['hit_count'] += $timer['hit_count'];
        $data[$v]['timers'][] = $timer;
    }

    return $data;
}

function formatRequestTimes($r) {
    $r['req_time'] = intval($r['req_time'] * 1000);
    $r['req_time_format'] = number_format($r['req_time'], 0, '.', ',');
    $r['mem_peak_usage_format']  = number_format($r['mem_peak_usage'], 0, '.', ',');

    $v = 0;
    foreach ($r['timers'] as &$group) {
        $group['value'] = intval($group['value'] * 1000);
        $v += $group['value'];
        $group['value_format'] = number_format($group['value'], 0, '.', ',');
        $group['value_percent'] = number_format($group['value'] / $r['req_time'] * 100, 2, '.', ',');

        foreach ($group['timers'] as &$timer) {
            $timer['value'] = intval($timer['value'] * 1000);
            $timer['value_format'] = number_format($timer['value'], 0, '.', ',');
            $timer['value_percent'] = number_format($timer['value'] / $r['req_time'] * 100, 2, '.', ',');
        }
    }

    $r['req_time_other'] = $r['req_time'] - $v;
    $r['req_time_other_format'] = number_format($r['req_time_other'], 0, '.', ',');
    $r['req_time_other_percent'] = number_format($r['req_time_other'] / $r['req_time'] * 100, 2, '.', ',');

    return $r;
}

return $server;
