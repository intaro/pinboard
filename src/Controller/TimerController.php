<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Utils\Utils;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TimerController extends AbstractController
{
//    private $app['controllers_factory'];
    private $requestTypes = ['live', 'req_time'];

    private EntityManagerInterface $entityManager;
    function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/timers/{type}/{requestId}/{grouping}', name: 'timers_show', methods: ['GET'], defaults: ['grouping' => 'grouping-none'], requirements: ['type' => 'live|req_time'])]
    #[Route('/{type}/{requestId}/{grouping}', name: 'timer_legacy', methods: ['GET'], defaults: ['grouping' => 'grouping-none'], requirements: ['type' => 'live|req_time'])]
    public function actionTimer($type, $requestId, $grouping): Response
    {
        if (!in_array($type, $this->requestTypes)) {
            throw $this->createNotFoundException("Type $type not allowed. Allowed types: " . implode(', ', $this->requestTypes));
        }

        $date = null;
        if (stripos($requestId, '::') !== false) {
            list($requestId, $date) = explode('::', $requestId);
        }

        $request = $this->getRequestById($this->entityManager, $type, $requestId, $date);
        if (!$request) {
            throw $this->createNotFoundException("Request #$requestId not found.");
        }

        $request['script_name'] = Utils::urlDecode($request['script_name']);
        $request = Utils::parseRequestTags($request);

        $request['timers'] = $this->getTimers($this->entityManager, $type, $requestId, $date);

        $groupingTags = $this->findGroupingTags($request['timers']);

        if (strlen($grouping)) {
            $grouping = preg_replace('/^grouping\-/', '', $grouping);
        }

        if (empty($grouping)) {
            if (count($groupingTags)) {
                if (in_array('group', $groupingTags)) {
                    $grouping = 'group';
                } else {
                    $grouping = $groupingTags[0];
                }
            } else {
                $grouping = null;
            }
        }

        //grouping turning off
        if ($grouping === 'none') {
            $grouping = null;
        }

        if ($grouping) {
            $request['timers'] = $this->groupTimers($request['timers'], $grouping);
        }

        $request = $this->formatRequestTimes($request);

        $result = [
            'request' => $request,
            'title' => "Timers for $type request #$requestId",
            'grouping_tags' => $groupingTags,
            'grouping' => $grouping,
            'type' => $type,
            'request_id' => "$requestId::$date",
//           Надо разобраться с этим base_url, сделать нормальную маршрутизацию
            'base_url' => '/'
        ];

        return $this->render('timer.html.twig', $result);
    }

    function getRequestById($conn, $type, $id, $date = null)
    {
        if ($type === 'live') {
            $params = [
                'id' => $id
            ];

            $sql = '
            SELECT
                *
            FROM
                request
            WHERE
                id = :id
        ';
        } else {
            $params = [
                'id' => $id,
                'date' => $date
            ];

//            $sql = '
//            SELECT
//                *
//            FROM
//                ipm_req_time_details
//            WHERE
//                request_id = :id AND created_at = :date
//        ';
            $sql = '
            SELECT
                *
            FROM
                ipm_req_time_details
            WHERE
                request_id = :id
        ';
        }

//        $data = $conn->fetchAll($sql, $params);
        $data = $conn->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        if (count($data)) {
            return $data[0];
        }

        return null;
    }

    function getTimers($conn, $type, $id, $date = null)
    {
        if ($type === 'live') {
            $params = [
                'id' => $id
            ];

            $sql = '
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
        ';

        } else {
            $params = [
                'id' => $id,
                'date' => $date
            ];

            $sql = '
            SELECT
                t.timer_id as id, t.hit_count, t.value, t.tag_name, t.tag_value
            FROM
                ipm_timer t
            WHERE
                request_id = :id AND created_at = :date
        ';
        }

//        $data = $conn->fetchAll($sql, $params);
        $data = $conn->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        if (!count($data)) {
            return [];
        }

        $timers = [];
        foreach ($data as $timer) {
            if (!isset($timers[$timer['id']])) {
                $timers[$timer['id']] = [
                    'id' => $timer['id'],
                    'hit_count' => $timer['hit_count'],
                    'value' => $timer['value'],
                    'tags' => [],
                ];
            }

            if (!in_array($timer['tag_name'], ['__hostname', '__server_name'])) {
                $timers[$timer['id']]['tags'][$timer['tag_name']] = $timer['tag_value'];
            }
        }

        unset($data);

        return $timers;
    }

    // Search tags which exist in all timers
    function findGroupingTags($timers)
    {
        if (!count($timers)) {
            return [];
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
                if ($tag === 'category') {
                    unset($tags[$index]);
                }
            }
        }

        return $tags;
    }

    function groupTimers($timers, $groupingTag)
    {
        $data = [];

        foreach ($timers as $timer) {
            $v = $timer['tags'][$groupingTag];
            $isComposite = false;

            if (preg_match('/(.+)\:\:(.*)/', $v, $matches)) {
                $v = $matches[1];
                $isComposite = true;
            }

            if (!isset($data[$v])) {
                $data[$v] = [
                    'value' => 0,
                    'hit_count' => 0
                ];
            }

            if ($isComposite) {
                $timer['tags']['operation'] = $matches[2];
            }

            unset($timer['tags'][$groupingTag]);

            if ($groupingTag === 'group' && isset($timer['tags']['category'])) {
                unset($timer['tags']['category']);
            }

            $data[$v]['value'] += $timer['value'];
            $data[$v]['hit_count'] += $timer['hit_count'];
            $data[$v]['timers'][] = $timer;
        }

        return $data;
    }

    function formatRequestTimes($r)
    {
        $r['req_time'] = intval($r['req_time'] * 1000);
        $r['req_time_format'] = number_format($r['req_time'], 0, '.', ',');
        if (isset($r['mem_peak_usage'])) {
            $r['mem_peak_usage_format'] = number_format($r['mem_peak_usage'], 0, '.', ',');
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
}
