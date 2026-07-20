<?php

declare(strict_types=1);

namespace App\Controller;

use App\Utils\DateTimeUtils;
use App\Utils\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

class TimerController extends AbstractController
{
    /** @var list<string> */
    private array $requestTypes = ['live', 'req_time'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/timers/{type}/{requestId}/{grouping}', name: 'timers_show', methods: ['GET'], defaults: ['grouping' => ''], requirements: ['type' => 'live|req_time'])]
    #[Route('/{type}/{requestId}/{grouping}', name: 'timer_legacy', methods: ['GET'], defaults: ['grouping' => ''], requirements: ['type' => 'live|req_time'])]
    public function actionTimer(string $type, string $requestId, string $grouping): Response
    {
        if (!in_array($type, $this->requestTypes)) {
            throw $this->createNotFoundException("Type $type not allowed. Allowed types: " . implode(', ', $this->requestTypes));
        }

        $date = null;
        if (stripos($requestId, '::') !== false) {
            list($requestId, $date) = explode('::', $requestId);
        }

        $request = $this->getRequestById($type, $requestId, $date);
        if (!$request) {
            throw $this->createNotFoundException("Request #$requestId not found.");
        }

        $serverName = is_string($request['server_name'] ?? null) ? $request['server_name'] : '';
        if (!Utils::userCanAccessServer($this->getUser(), $serverName)) {
            throw new AccessDeniedHttpException('Access to this server is not allowed for your account.');
        }

        $request['script_name'] = Utils::urlDecode(is_string($request['script_name']) ? $request['script_name'] : '');
        if ($type === 'req_time') {
            $createdAt = is_string($request['created_at'] ?? null) ? $request['created_at'] : '';
            $request['created_at_format'] = DateTimeUtils::formatStorageDateTimeForServer($createdAt);
        } elseif (is_numeric($request['timestamp'] ?? null)) {
            $request['timestamp_format'] = DateTimeUtils::formatUnixTimestampForServer((int) $request['timestamp']);
        }
        $request = Utils::parseRequestTags($request);
        if (!is_array($request)) {
            throw $this->createNotFoundException("Request #$requestId not found.");
        }

        $request['timers'] = $this->getTimers($type, $requestId, $date);

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
            'menu' => $this->buildMenu(),
        ];

        return $this->render('timer.html.twig', $result);
    }

    /** @return array<string, mixed>|null */
    private function getRequestById(string $type, string $id, ?string $date = null): ?array
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
            ];

            $dateCondition = '';
            if ($date !== null && $date !== '') {
                $params['date'] = $date;
                $dateCondition = ' AND created_at = :date';
            }

            $sql = '
            SELECT
                *
            FROM
                ipm_req_time_details
            WHERE
                request_id = :id' . $dateCondition . '
        ';
        }

        $data = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        if (count($data)) {
            return $data[0];
        }

        return null;
    }

    /** @return array<int|string, array{id: int|string, hit_count: int|float, value: int|float, tags: array<string, mixed>}> */
    private function getTimers(string $type, string $id, ?string $date = null): array
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

        $data = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        if (!count($data)) {
            return [];
        }

        $timers = [];
        foreach ($data as $timer) {
            $timerId = is_int($timer['id']) ? $timer['id'] : (is_string($timer['id']) ? $timer['id'] : '');
            $tagName = is_string($timer['tag_name']) ? $timer['tag_name'] : '';
            if (!isset($timers[$timerId])) {
                $timers[$timerId] = [
                    'id' => $timerId,
                    'hit_count' => is_numeric($timer['hit_count']) ? (float) $timer['hit_count'] : 0.0,
                    'value' => is_numeric($timer['value']) ? (float) $timer['value'] : 0.0,
                    'tags' => [],
                ];
            }

            if ($tagName !== '' && !in_array($tagName, ['__hostname', '__server_name'])) {
                $timers[$timerId]['tags'][$tagName] = $timer['tag_value'];
            }
        }

        unset($data);

        return $timers;
    }

    // Search tags which exist in all timers
    /**
     * @param array<int|string, array{id: int|string, hit_count: int|float, value: int|float, tags: array<string, mixed>}> $timers
     * @return list<string>
     */
    private function findGroupingTags(array $timers): array
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

        return array_values($tags);
    }

    /**
     * @param array<int|string, array{id: int|string, hit_count: int|float, value: int|float, tags: array<string, mixed>}> $timers
     * @return array<string, array{value: int|float, hit_count: int|float, timers: list<array<string, mixed>>}>
     */
    private function groupTimers(array $timers, string $groupingTag): array
    {
        $data = [];

        foreach ($timers as $timer) {
            $tagVal = $timer['tags'][$groupingTag] ?? null;
            $v = is_string($tagVal) ? $tagVal : '';
            $isComposite = false;

            if (preg_match('/(.+)\:\:(.*)/', $v, $matches)) {
                $v = $matches[1];
                $isComposite = true;
            }

            if (!isset($data[$v])) {
                $data[$v] = [
                    'value' => 0,
                    'hit_count' => 0,
                    'timers' => [],
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

    /**
     * @param array<string, mixed> $r
     * @return array<string, mixed>
     */
    private function formatRequestTimes(array $r): array
    {
        $reqTime = (int) ((is_numeric($r['req_time']) ? (float) $r['req_time'] : 0.0) * 1000);
        $r['req_time'] = $reqTime;
        $r['req_time_format'] = number_format($reqTime, 0, '.', ',');
        if (isset($r['mem_peak_usage']) && is_numeric($r['mem_peak_usage'])) {
            $r['mem_peak_usage_format'] = number_format((float) $r['mem_peak_usage'], 0, '.', ',');
        }

        $v = 0;
        $timers = is_array($r['timers']) ? $r['timers'] : [];
        foreach ($timers as $k => $group) {
            if (!is_array($group)) {
                continue;
            }
            $groupValue = (int) ((is_numeric($group['value'] ?? null) ? (float) $group['value'] : 0.0) * 1000);
            $v += $groupValue;
            $group['value'] = $groupValue;
            $group['value_format'] = number_format($groupValue, 0, '.', ',');
            $group['value_percent'] = number_format($reqTime > 0 ? $groupValue / $reqTime * 100 : 0, 2, '.', ',');

            if (isset($group['timers']) && is_array($group['timers'])) {
                foreach ($group['timers'] as $tk => $timer) {
                    if (!is_array($timer)) {
                        continue;
                    }
                    $timerValue = (int) ((is_numeric($timer['value'] ?? null) ? (float) $timer['value'] : 0.0) * 1000);
                    $timer['value'] = $timerValue;
                    $timer['value_format'] = number_format($timerValue, 0, '.', ',');
                    $timer['value_percent'] = number_format($reqTime > 0 ? $timerValue / $reqTime * 100 : 0, 2, '.', ',');
                    $group['timers'][$tk] = $timer;
                }
            }

            $timers[$k] = $group;
        }
        $r['timers'] = $timers;

        if ($reqTime - $v >= 0) {
            $other = $reqTime - $v;
            $r['req_time_other'] = $other;
            $r['req_time_other_format'] = number_format($other, 0, '.', ',');
            $r['req_time_other_percent'] = number_format($reqTime > 0 ? $other / $reqTime * 100 : 0, 2, '.', ',');
        }

        return $r;
    }

    /** @return array<string, mixed> */
    private function buildMenu(): array
    {
        return (new BeforeController($this->entityManager))->actionBefore(Utils::getUserHostsRegexp($this->getUser()));
    }
}
