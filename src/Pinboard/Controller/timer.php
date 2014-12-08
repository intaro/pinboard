<?php

use Pinboard\Utils\Utils;
use Symfony\Component\HttpFoundation\Request;

$server = $app['controllers_factory'];
$requestTypes = array('live', 'req_time');

$server->get('/{type}/{requestId}/{grouping}', function($type, $requestId, $grouping) use ($app, $requestTypes) {
    if (!in_array($type, $requestTypes)) {
        $app->abort(404, "Type $type not allowed. Allowed types: " . implode(', ', $requestTypes));
    }

    $date = null;
    if (stripos($requestId, '::') !== false) {
        list($requestId, $date) = explode('::', $requestId);
    }

    $request = getRequestById($app['db'], $type, $requestId, $date);
    if (!$request) {
        $app->abort(404, "Request #$requestId not found.");
    }

    Utils::checkUserAccess($app, $request['server_name']);

    $request['script_name'] = Utils::urlDecode($request['script_name']);
    $request = Utils::parseRequestTags($request);

    $request['timers'] = getTimers($app['db'], $type, $requestId, $date);

    $groupingTags = findGroupingTags($request['timers']);

    if (strlen($grouping)) {
        $grouping = preg_replace('/^grouping\-/', '', $grouping);
    }
    if (empty($grouping)) {
        if (sizeof($groupingTags)) {
            if (in_array('group', $groupingTags)) {
                $grouping = 'group';
            }
            else {
                $grouping = $groupingTags[0];
            }
        }
        else {
            $grouping = null;
        }
    }
    //grouping turning off
    if ($grouping == 'none') {
        $grouping = null;
    }

    if ($grouping) {
        $request['timers'] = groupTimers($request['timers'], $grouping);
    }

    $request = formatRequestTimes($request);

    $result = array(
        'request' => $request,
        'title' => "Timers for $type request #$requestId",
        'grouping_tags' => $groupingTags,
        'grouping' => $grouping,
        'type' => $type,
        'request_id' => $requestId.'::'.$date,
    );

    return $app['twig']->render(
        'timer.html.twig',
        $result
    );
})
->value('type', 'live')
->value('grouping', null)
//->assert('requestId', '\d+')
->bind('timers_show');

function getRequestById($conn, $type, $id, $date = null) {
    if ($type == 'live') {
        $params = array(
            'id' => $id,
        );

        $sql = "
            SELECT
                *
            FROM
                request
            WHERE
                id = :id
        ";
    }
    else {
        $params = array(
            'id' => $id,
            'date' => $date,
        );

        $sql = "
            SELECT
                *
            FROM
                ipm_req_time_details
            WHERE
                request_id = :id AND created_at = :date
        ";
    }

    $data = $conn->fetchAll($sql, $params);

    if (sizeof($data)) {
        return $data[0];
    }

    return null;
}

function getTimers($conn, $type, $id, $date = null) {
    if ($type == 'live') {
        $params = array(
            'id' => $id,
        );

        $sql = "
            SELECT
                t.id, t.hit_count, t.value, tag.name as tag_name, tt.value as tag_value
            FROM
                timer t
            JOIN
                timertag tt ON tt.timer_id = t.id
            JOIN
                tag ON tt.tag_id = tag.id
            WHERE
                t.request_id = :id
        ";
    }
    else {
        $params = array(
            'id' => $id,
            'date' => $date,
        );

        $sql = "
            SELECT
                t.timer_id as id, t.hit_count, t.value, t.tag_name, t.tag_value
            FROM
                ipm_timer t
            WHERE
                request_id = :id AND created_at = :date
        ";
    }

    $data = $conn->fetchAll($sql, $params);

    if (!sizeof($data)) {
        return array();
    }

    $timers = array();
    foreach ($data as $timer) {
        if (!isset($timers[$timer['id']])) {
            $timers[$timer['id']] = array(
                'id' => $timer['id'],
                'hit_count' => $timer['hit_count'],
                'value' => $timer['value'],
                'tags' => array(),
            );
        }
        if (!in_array($timer['tag_name'], array('__hostname', '__server_name'))) {
            $timers[$timer['id']]['tags'][$timer['tag_name']] = $timer['tag_value'];
        }
    }
    unset($data);

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

    //if group is defined then remove category
    if (in_array('group', $tags)) {
        foreach ($tags as $index => $tag) {
            if ($tag == 'category') {
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
        $isComposite = false;
        if (preg_match('/(.+)\:\:(.*)/', $v, $matches)) {
            $v = $matches[1];
            $isComposite = true;
        }
        if (!isset($data[$v])) {
            $data[$v] = array(
                'value' => 0,
                'hit_count' => 0,
            );
        }
        if ($isComposite) {
            $timer['tags']['operation'] = $matches[2];
        }
        unset($timer['tags'][$groupingTag]);
        if ($groupingTag == 'group' && isset($timer['tags']['category'])) {
            unset($timer['tags']['category']);
        }
        $data[$v]['value'] += $timer['value'];
        $data[$v]['hit_count'] += $timer['hit_count'];
        $data[$v]['timers'][] = $timer;
    }

    return $data;
}

function formatRequestTimes($r) {
    $r['req_time'] = intval($r['req_time'] * 1000);
    $r['req_time_format'] = number_format($r['req_time'], 0, '.', ',');
    if (isset($r['mem_peak_usage'])) {
        $r['mem_peak_usage_format']  = number_format($r['mem_peak_usage'], 0, '.', ',');
    }

    $v = 0;
    foreach ($r['timers'] as &$group) {
        $group['value'] = intval($group['value'] * 1000);
        $v += $group['value'];
        $group['value_format'] = number_format($group['value'], 0, '.', ',');
        $group['value_percent'] = number_format($group['value'] / $r['req_time'] * 100, 2, '.', ',');

        if (isset($group['timers'])) {
            foreach ($group['timers'] as &$timer) {
                $timer['value'] = intval($timer['value'] * 1000);
                $timer['value_format'] = number_format($timer['value'], 0, '.', ',');
                $timer['value_percent'] = number_format($timer['value'] / $r['req_time'] * 100, 2, '.', ',');
            }
        }
    }

    if ($r['req_time'] - $v >= 0) {
        $r['req_time_other'] = $r['req_time'] - $v;
        $r['req_time_other_format'] = number_format($r['req_time_other'], 0, '.', ',');
        $r['req_time_other_percent'] = number_format($r['req_time_other'] / $r['req_time'] * 100, 2, '.', ',');
    }

    return $r;
}

return $server;
