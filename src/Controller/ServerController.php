<?php

declare(strict_types=1);

namespace App\Controller;

use App\Command\AggregateCommand;
use App\Utils\DateTimeUtils;
use App\Utils\SqlUtils;
use App\Utils\Utils;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Attribute\Route;

class ServerController extends AbstractController
{
    private const ROW_PER_PAGE = 50;

    private readonly int $rowPerPage;

    /** @var list<string> */
    private array $allowedPeriods = ['1 day', '3 days', '1 week', '1 month'];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        #[Autowire('%env(int:APP_PAGINATION_ROW_PER_PAGE)%')] int $rowPerPage
    ) {
        $this->rowPerPage = $rowPerPage > 0 ? $rowPerPage : self::ROW_PER_PAGE;
    }

    private function assertServerAccess(string $serverName): void
    {
        if (!Utils::userCanAccessServer($this->getUser(), $serverName)) {
            throw new AccessDeniedHttpException('Access to this server is not allowed for your account.');
        }
    }

    #[Route('/{serverName}/{hostName}/overview.{format}', name: 'server', methods: ['GET'])]
    public function actionOverview(Request $request, string $serverName, string $hostName, string $format): Response
    {
        $this->assertServerAccess($serverName);
        $period = $request->query->get('period', '1 day');
        if (!in_array($period, $this->allowedPeriods)) {
            $period = '1 day';
        }

        $result = [];
        $result['hosts'] = $this->getHosts($serverName);
        $result['req'] = $this->getRequestReview($serverName, $hostName, $period);
        $result['req_per_sec'] = $this->getRequestPerSecReview($serverName, $hostName, $period);
        $result['statuses'] = $this->getStatusesReview($serverName, $hostName, $period);

        if ($format === 'html') {
            $result['server_name'] = $serverName;
            $result['hostname'] = $hostName;
            $result['period'] = $period;
            $result['periods'] = $this->allowedPeriods;
            $result['title'] = $serverName;
            $result['req_time_border'] = number_format((float) $this->getReqTimeBorder($serverName) * 1000, 0, '.', '');
            $result['menu'] = $this->buildMenu();

            return $this->render('server.html.twig', $result);
        }

        if ($format === 'json') {
            unset($result['hosts']);

            foreach ($result['req'] as &$value) {
                unset($value['date']);
                unset($value['label']);
            }

            $allPoints = [];
            foreach ($result['req_per_sec']['data'] as $value) {
                foreach ($value as $item) {
                    $allPoints[] = $item;
                }
            }

            $result['req_per_sec']['data'] = $allPoints;

            if (isset($result['req_per_sec']['hosts']['_'])) {
                foreach ($result['req_per_sec']['data'] as &$value) {
                    if ($value['parsed_hostname'] === '_') {
                        unset($value['date']);
                        unset($value['label']);
                        unset($value['parsed_hostname']);
                        unset($value['hostname']);

                        $result['req_per_sec'][] = $value;
                    }
                }

                unset($result['req_per_sec']['data']);
                unset($result['req_per_sec']['hosts']);
            } else {
                $result['req_per_sec'] = $result['req_per_sec']['data'];

                foreach ($result['req_per_sec'] as &$value) {
                    unset($value['date']);
                    unset($value['label']);
                    unset($value['parsed_hostname']);
                }
            }

            foreach ($result['statuses']['data'] as &$value) {
                unset($value['date']);
                unset($value['label']);
            }

            $result['success'] = 'true';

            $response = new JsonResponse($result);
            $response->setStatusCode(200);

            return $response;
        }

        throw $this->createNotFoundException(sprintf('Unsupported format "%s".', $format));
    }

    #[Route('/{serverName}/{hostName}/timers', name: 'server_timers', methods: ['GET'])]
    public function actionTimers(Request $request, string $serverName, string $hostName): Response
    {
        $this->assertServerAccess($serverName);
        $period = $request->query->get('period', '1 day');
        if (!in_array($period, $this->allowedPeriods)) {
            $period = '1 day';
        }

        $serverFilter = $request->query->get('server', 'off');
        if (!in_array($serverFilter, ['on', 'off'])) {
            $serverFilter = 'off';
        }
        $serverFilter = $serverFilter === 'on';

        $result = [
            'hosts' => $this->getHosts($serverName),
            'title' => $serverName,
            'periods' => $this->allowedPeriods,
            'period' => $period,
            'server_filter' => $serverFilter,
            'server_name' => $serverName,
            'hostname' => $hostName,
            'charts' => [
                /*'timer_median' => [
                    'title' => 'Request time',
                    'subtitle' => 'median',
                    'field' => 'timer_median',
                    'unit' => ' ms',
                    'data' => getTimersList($app['db'], $serverName, $hostName, 'timer_median', $period, $serverFilter),
                ],
                'timer_p95' => [
                    'title' => 'Request time',
                    'subtitle' => '95th percentile',
                    'field' => 'p95',
                    'unit' => ' ms',
                    'data' => getTimersList($app['db'], $serverName, $hostName, 'timer_p95', $period, $serverFilter),
                ],*/
                'hit_count' => [
                    'title' => 'Hit count',
                    'field' => 'hit_count',
                    'unit' => '',
                    'data' => $this->getTimersList($serverName, $hostName, 'hit_count', $period, $serverFilter),
                ],
                'timer_value' => [
                    'title' => 'Timer value',
                    'subtitle' => 'total',
                    'field' => 'timer_value',
                    'unit' => ' s',
                    'data' => $this->getTimersList($serverName, $hostName, 'timer_value', $period, $serverFilter),
                ],
            ],
            'request_graphs' => [
                'req_time_median' => [
                    'title' => 'Request time (median)',
                ],
                'req_time_p95' => [
                    'title' => 'Request time (95th percentile)',
                ],
                'req_time_total' => [
                    'title' => 'Total request time',
                ],
            ],
        ];

        $result['menu'] = $this->buildMenu();

        return $this->render('timers.html.twig', $result);
    }

    #[Route('/{serverName}/{hostName}/statuses/{pageNum}/{colOrder}/{colDir}', name: 'server_statuses', methods: ['GET'])]
    public function actionStatuses(Request $request, string $serverName, string $hostName, string $pageNum, string $colOrder, string $colDir): Response
    {
        $this->assertServerAccess($serverName);
        $pageNum = (int) str_replace('page', '', $pageNum);

        $result = [
            'server_name' => $serverName,
            'hostname' => $hostName,
            'title' => "Error pages / $serverName",
            'pageNum' => $pageNum,
            'colOrder' => $colOrder,
            'colDir' => $colDir
        ];

        $result['rowPerPage'] = $this->rowPerPage;

        $rowCount = $this->getErrorPagesCount($serverName, $hostName);
        $result['rowCount'] = $rowCount;

        $pageCount = ceil($rowCount / $this->rowPerPage);
        $result['pageCount'] = $pageCount;

        if ((int)$pageCount !== 0) {
            if ($pageNum < 1 || $pageNum > $pageCount) {
                throw $this->createNotFoundException("Page $pageNum does not exist.");
            }
        }

        $startPos = ($pageNum - 1) * $this->rowPerPage;
        $result['hosts'] = $this->getHosts($serverName);
        $result['statuses'] = $this->getErrorPages($serverName, $hostName, $startPos, $this->rowPerPage, $colOrder, $colDir);

        $result['menu'] = $this->buildMenu();

        return $this->render('statuses.html.twig', $result);
    }

    #[Route('/{serverName}/{hostName}/req-time/{pageNum}/{colOrder}/{colDir}', name: 'server_req_time', methods: ['GET'])]
    public function actionReqTime(Request $request, string $serverName, string $hostName, string $pageNum, string $colOrder, string $colDir): Response
    {
        $this->assertServerAccess($serverName);
        $pageNum = (int) str_replace('page', '', $pageNum);

        $result = [
            'server_name' => $serverName,
            'hostname' => $hostName,
            'title' => "Request time / $serverName",
            'pageNum' => $pageNum,
            'colOrder' => $colOrder,
            'colDir' => $colDir
        ];

        $result['rowPerPage'] = $this->rowPerPage;

        $rowCount = $this->getSlowPagesCount($serverName, $hostName);
        $result['rowCount'] = $rowCount;

        $pageCount = ceil($rowCount / $this->rowPerPage);
        $result['pageCount'] = $pageCount;
        if ((int)$pageCount !== 0) {
            if ($pageNum < 1 || $pageNum > $pageCount) {
                throw $this->createNotFoundException("Page $pageNum does not exist.");
            }
        }
        $startPos = ($pageNum - 1) * $this->rowPerPage;

        $result['hosts'] = $this->getHosts($serverName);
        $result['pages'] = $this->getSlowPages($serverName, $hostName, $startPos, $this->rowPerPage, $colOrder, $colDir);

        $result['menu'] = $this->buildMenu();

        return $this->render('req_time.html.twig', $result);
    }

    #[Route('/{serverName}/{hostName}/mem-usage/{pageNum}/{colOrder}/{colDir}', name: 'server_mem_usage', methods: ['GET'])]
    public function actionMemUsage(Request $request, string $serverName, string $hostName, string $pageNum, string $colOrder, string $colDir): Response
    {
        $this->assertServerAccess($serverName);
        $pageNum = (int) str_replace('page', '', $pageNum);

        $result = [
            'server_name' => $serverName,
            'hostname' => $hostName,
            'title' => "Memory peak usage / $serverName",
            'pageNum' => $pageNum,
            'colOrder' => $colOrder,
            'colDir' => $colDir
        ];

        $result['rowPerPage'] = $this->rowPerPage;

        $rowCount = $this->getHeavyPagesCount($serverName, $hostName);
        $result['rowCount'] = $rowCount;

        $pageCount = ceil($rowCount / $this->rowPerPage);
        $result['pageCount'] = $pageCount;
        if ((int)$pageCount !== 0) {
            if ($pageNum < 1 || $pageNum > $pageCount) {
                throw $this->createNotFoundException("Page $pageNum does not exist.");
            }
        }
        $startPos = ($pageNum - 1) * $this->rowPerPage;

        $result['hosts'] = $this->getHosts($serverName);
        $result['pages'] = $this->getHeavyPages($serverName, $hostName, $startPos, $this->rowPerPage, $colOrder, $colDir);

        $result['menu'] = $this->buildMenu();

        return $this->render('mem_usage.html.twig', $result);
    }

    #[Route('/{serverName}/{hostName}/cpu-usage/{pageNum}/{colOrder}/{colDir}', name: 'server_cpu_usage', methods: ['GET'])]
    public function actionCpuUsage(Request $request, string $serverName, string $hostName, string $pageNum, string $colOrder, string $colDir): Response
    {
        $this->assertServerAccess($serverName);
        $pageNum = (int) str_replace('page', '', $pageNum);

        $result = [
            'server_name' => $serverName,
            'hostname' => $hostName,
            'title' => "CPU peak usage / $serverName",
            'pageNum' => $pageNum,
            'colOrder' => $colOrder,
            'colDir' => $colDir
        ];

        $result['rowPerPage'] = $this->rowPerPage;

        $rowCount = $this->getCPUPagesCount($serverName, $hostName);
        $result['rowCount'] = $rowCount;

        $pageCount = ceil($rowCount / $this->rowPerPage);
        $result['pageCount'] = $pageCount;
        if ((int)$pageCount !== 0) {
            if ($pageNum < 1 || $pageNum > $pageCount) {
                throw $this->createNotFoundException("Page $pageNum does not exist.");
            }
        }
        $startPos = ($pageNum - 1) * $this->rowPerPage;

        $result['hosts'] = $this->getHosts($serverName);
        $result['pages'] = $this->getCPUPages($serverName, $hostName, $startPos, $this->rowPerPage, $colOrder, $colDir);

        $result['menu'] = $this->buildMenu();

        return $this->render('cpu_usage.html.twig', $result);
    }

    #[Route('/{serverName}/{hostName}/live', name: 'server_live', methods: ['GET', 'POST'])]
    public function actionLive(Request $request, string $serverName, string $hostName): Response
    {
        $this->assertServerAccess($serverName);
        $session = $request->hasSession() ? $request->getSession() : null;

        $serverFilter = $this->loadServerFilter($session?->get('filter_params'), $serverName);

        $result = [
            'server_name' => $serverName,
            'hostname' => $hostName,
            'title' => "Live / $serverName",
            'limit' => 100
        ];

        foreach (['req_time', 'script_name', 'tags'] as $key) {
            $serverFilter[$key] = $request->request->get($key);
        }

        if ($request->isXmlHttpRequest()) {
            $result['limit'] = 50;

            $allFilters = $this->loadAllFilters($session?->get('filter_params'));
            $allFilters[$serverName] = $serverFilter;
            $session?->set('filter_params', $allFilters);

            $serverFilter['last_id'] = $request->request->get('last_id');
            $serverFilter['last_timestamp'] = $request->request->get('last_timestamp');
        } else {
            $result['filter'] = $serverFilter;
            $result['show_filter'] = false;

            foreach ($serverFilter as $item) {
                if ($item) {
                    $result['show_filter'] = true;
                    break;
                }
            }
        }

        $result['pages'] = $this->getLivePages($serverName, $hostName, $serverFilter, $result['limit']);

        $ids = [];
        foreach ($result['pages'] as $item) {
            $id = $item['id'];
            if (is_string($id) || is_int($id)) {
                $ids[] = $id;
            }
        }
        $addData = $this->getTagsTimersForIds($ids);

        $tagsFilter = [];
        $tagsRaw = $serverFilter['tags'] ?? null;
        if (is_string($tagsRaw) && preg_match_all(
            '/([\w\:-_]+)\s*?\=\s*?([\w\:-_]+)/',
            $tagsRaw,
            $matches,
            PREG_SET_ORDER
        )) {
            foreach ($matches as $match) {
                $tagsFilter[$match[1]] = $match[2];
            }
        }

        foreach ($addData as $addItem) {
            foreach ($result['pages'] as $k => $item) {
                if ($addItem['id'] === $item['id']) {
                    foreach ($addItem as $key => $value) {
                        $result['pages'][$k][$key] = $value;
                    }

                    $item = Utils::parseRequestTags($result['pages'][$k], $tagsFilter);
                    if (!$item) {
                        unset($result['pages'][$k]);
                    } else {
                        $result['pages'][$k] = $item;
                    }
                }
            }
        }
        $result['pages'] = array_values($result['pages']);

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse($result);
        } else {
            $result['hosts'] = $this->getHosts($serverName);
            $result['last_id'] = count($result['pages']) ? $result['pages'][0]['id'] : 0;
            $result['last_timestamp'] = count($result['pages']) ? $result['pages'][0]['timestamp'] : 0;
            $result['menu'] = $this->buildMenu();

            $response = $this->render('live.html.twig', $result);

            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');

            return $response;
        }
    }


    /** @return array<string, mixed> */
    private function buildMenu(): array
    {
        $hostsRegexp = Utils::getUserHostsRegexp($this->getUser());
        return (new BeforeController($this->entityManager))->actionBefore($hostsRegexp);
    }

    public function getReqTimeBorder(string $serverName): float
    {
        return AggregateCommand::DEFAULT_REQ_TIME_BORDER;
    }

    /** @return list<string> */
    private function getHosts(string $serverName): array
    {
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

        $hosts = $this->entityManager->getConnection()->executeQuery($sql, ['server_name' => $serverName])->fetchAllAssociative();

        return array_map(static fn (array $row): string => is_string($row['hostname']) ? $row['hostname'] : '', $hosts);
    }

    /** @return array{data: list<array<string, mixed>>, codes: array<string, string>} */
    private function getStatusesReview(string $serverName, string $hostName, string $period): array
    {
        $dateSelect = SqlUtils::getDateSelectExpression($period);
        $params = [
            'server_name' => $serverName,
            'created_at' => DateTimeUtils::storageDateTimeAgo($period)
        ];

        $hostCondition = '';
        if ($hostName !== 'all') {
            $params['hostname'] = $hostName;
            $hostCondition = 'AND hostname = :hostname';
        }

        $sql = "
            SELECT
                $dateSelect as created_at, status, count(*) as cnt
            FROM
                ipm_status_details
            WHERE
                server_name = :server_name
                $hostCondition
                AND created_at > :created_at
            GROUP BY
                " . SqlUtils::getDateGroupExpression($period) . ', status
            ORDER BY
                created_at
        ';

        $stmt = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        $data = [];
        $codes = [];

        foreach ($stmt as $row) {
            $createdAt = is_string($row['created_at']) ? $row['created_at'] : '';
            $statusCode = is_string($row['status']) ? $row['status'] : (is_int($row['status']) ? (string) $row['status'] : '');

            $data[] = [
                'created_at' => $createdAt,
                'date' => DateTimeUtils::chartDateFromStorageDateTime($createdAt),
                'label' => DateTimeUtils::chartLabelFromStorageDateTime($createdAt),
                'error_code' => $statusCode,
                'error_count' => $row['cnt'],
            ];

            if (!isset($codes[$statusCode])) {
                $codes[$statusCode] = Utils::generateColor();
            }
        }
        ksort($codes);

        return ['data' => $data, 'codes' => $codes];
    }

    /** @return array{data: array<string, list<array<string, mixed>>>, hosts: array<string, array<string, string>>} */
    private function getRequestPerSecReview(string $serverName, string $hostName, string $period): array
    {
        $dateSelect = SqlUtils::getDateSelectExpression($period);
        $params = [
            'server_name' => $serverName,
            'created_at' => DateTimeUtils::storageDateTimeAgo($period)
        ];

        $hostCondition = '';
        if ($hostName !== 'all') {
            $params['hostname'] = $hostName;
            $hostCondition = 'AND hostname = :hostname';
        }

        $sql = "
            SELECT
                $dateSelect as created_at, avg(req_per_sec) as req_per_sec, hostname
            FROM
                ipm_report_by_hostname_and_server
            WHERE
                server_name = :server_name
                $hostCondition
                AND created_at > :created_at
            GROUP BY
                " . SqlUtils::getDateGroupExpression($period) . ', hostname
            ORDER BY
                created_at
        ';

        $data = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        $rpqData = [];
        $rpqHosts = [];
        $hostCount = 0;

        foreach ($data as $item) {
            $createdAt = is_string($item['created_at']) ? $item['created_at'] : '';
            $hostname = is_string($item['hostname']) ? $item['hostname'] : '';
            $reqPerSec = is_numeric($item['req_per_sec']) ? (float) $item['req_per_sec'] : 0.0;
            $date = DateTimeUtils::chartDateFromStorageDateTime($createdAt);
            $label = DateTimeUtils::chartLabelFromStorageDateTime($createdAt);
            $parsedHostname = '_' . preg_replace('/\W/', '_', $hostname);

            $rpqData[$date][] = [
                'created_at' => $createdAt,
                'label' => $label,
                'hostname' => $hostname,
                'parsed_hostname' => $parsedHostname,
                'req_per_sec' => number_format($reqPerSec, 2, '.', ''),
            ];

            if (!isset($rpqHosts[$parsedHostname])) {
                $rpqHosts[$parsedHostname] = [
                    'color' => Utils::generateColor(),
                    'host' => $hostname,
                ];
                $hostCount++;
            }
        }

        if ($hostCount > 1) {
            $sql = '
                SELECT
                    ' . $dateSelect . ' as created_at, avg(req_per_sec) as req_per_sec
                FROM
                    ipm_report_by_server_name USE INDEX (irsn_ca)
                WHERE
                    server_name = :server_name
                    AND created_at > :created_at
                GROUP BY
                    ' . SqlUtils::getDateGroupExpression($period) . '
                ORDER BY
                    created_at
            ';

            $data = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();
            $rpqHosts['_'] = ['color' => Utils::generateColor(), 'host' => '_'];

            foreach ($data as $item) {
                $createdAt = is_string($item['created_at']) ? $item['created_at'] : '';
                $reqPerSec = is_numeric($item['req_per_sec']) ? (float) $item['req_per_sec'] : 0.0;
                $date = DateTimeUtils::chartDateFromStorageDateTime($createdAt);
                $label = DateTimeUtils::chartLabelFromStorageDateTime($createdAt);

                $rpqData[$date][] = [
                    'created_at' => $createdAt,
                    'label' => $label,
                    'hostname' => '_',
                    'parsed_hostname' => '_',
                    'req_per_sec' => number_format($reqPerSec, 2, '.', ''),
                ];
            }
        }

        ksort($rpqHosts);

        return ['data' => $rpqData, 'hosts' => $rpqHosts];
    }

    /** @return list<array<string, mixed>> */
    private function getRequestReview(string $serverName, string $hostName, string $period): array
    {
        $dateSelect = SqlUtils::getDateSelectExpression($period);
        $params = [
            'server_name' => $serverName,
            'created_at' => DateTimeUtils::storageDateTimeAgo($period),
        ];

        $selectFields = '
            avg(req_time_90) as req_time_90, avg(req_time_95) as req_time_95,
            avg(req_time_99) as req_time_99, avg(req_time_100) as req_time_100,
            avg(mem_peak_usage_90) as mem_peak_usage_90, avg(mem_peak_usage_95) as mem_peak_usage_95,
            avg(mem_peak_usage_99) as mem_peak_usage_99, avg(mem_peak_usage_100) as mem_peak_usage_100,
            avg(cpu_peak_usage_90) as cpu_peak_usage_90, avg(cpu_peak_usage_95) as cpu_peak_usage_95,
            avg(cpu_peak_usage_99) as cpu_peak_usage_99, avg(cpu_peak_usage_100) as cpu_peak_usage_100
        ';

        $groupBy = 'GROUP BY ' . SqlUtils::getDateGroupExpression($period);
        $hostCondition = '';

        if ($hostName !== 'all') {
            $params['hostname'] = $hostName;
            $hostCondition = 'AND hostname = :hostname';
        }

        $sql = "
            SELECT
                $dateSelect as created_at,
                $selectFields
            FROM
                ipm_report_2_by_hostname_and_server
            WHERE
                server_name = :server_name
                $hostCondition
                AND created_at > :created_at
            $groupBy
            ORDER BY
                created_at
        ";

        $data = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        $result = [];
        foreach ($data as $item) {
            $createdAt = is_string($item['created_at']) ? $item['created_at'] : '';
            $row = [
                'created_at' => $createdAt,
                'date' => DateTimeUtils::chartDateFromStorageDateTime($createdAt),
                'label' => DateTimeUtils::chartLabelFromStorageDateTime($createdAt),
            ];

            foreach (['90', '95', '99', '100'] as $percent) {
                $raw = $item['req_time_' . $percent] ?? null;
                $row['req_time_' . $percent] = number_format(is_numeric($raw) ? (float) $raw * 1000 : 0.0, 0, '.', '');
            }
            foreach (['90', '95', '99', '100'] as $percent) {
                $raw = $item['mem_peak_usage_' . $percent] ?? null;
                $row['mem_peak_usage_' . $percent] = number_format(is_numeric($raw) ? (float) $raw : 0.0, 0, '.', '');
            }
            foreach (['90', '95', '99', '100'] as $percent) {
                $raw = $item['cpu_peak_usage_' . $percent] ?? null;
                $row['cpu_peak_usage_' . $percent] = number_format(is_numeric($raw) ? (float) $raw : 0.0, 3, '.', ',');
            }
            $result[] = $row;
        }

        return $result;
    }

    /** @return array{timers: list<string>, values: array<string, array<string, mixed>>} */
    private function getTimersList(string $serverName, string $hostName, string $valueField, string $period, bool $serverFilter): array
    {
        $params = [
            'server_name' => $serverName,
            'created_at' => DateTimeUtils::storageDateTimeAgo($period)
        ];
        $dateSelect = SqlUtils::getDateSelectExpression($period);
        $timeGroupBy = SqlUtils::getDateGroupExpression($period);

        $aggregation = [
            'hit_count' => [
                'agg' => 'sum(hit_count)'
            ],
            'timer_value' => [
                'agg' => 'sum(timer_value)',
                'req_field' => 'req_time_total',
                'req_agg' => 'sum(req_time_total)'
            ],
            'timer_median' => [
                'agg' => 'max(timer_median)',
                'req_field' => 'req_time_median',
                'req_agg' => 'max(req_time_median)'
            ],
            'timer_p95' => [
                'agg' => 'max(p95)',
                'req_field' => 'req_time_p95',
                'req_agg' => 'max(p95)'
            ]
        ];

        $hostCondition = 'AND hostname IS NULL';
        if ($hostName !== 'all') {
            $params['hostname'] = $hostName;
            $hostCondition = 'AND hostname = :hostname';
        }

        $serverCondition = $serverFilter ? 'AND server IS NOT NULL' : 'AND server IS NULL';
        $timers = [];
        $result = [];

        if (isset($aggregation[$valueField]['req_field'])) {
            $sql = "
                SELECT
                    {$aggregation[$valueField]['req_agg']} as {$aggregation[$valueField]['req_field']}, $dateSelect as created_at
                FROM
                    " . ($hostName !== 'all' ? 'ipm_report_by_hostname_and_server' : 'ipm_report_by_server_name') . '
                WHERE
                    server_name = :server_name
                    ' . ($hostName !== 'all' ? $hostCondition : '') . "
                    AND created_at > :created_at
                GROUP BY
                    $timeGroupBy
                ORDER BY
                    created_at ASC
            ";

            $data = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

            $reqField = $aggregation[$valueField]['req_field'];
            foreach ($data as $item) {
                $createdAt = is_string($item['created_at']) ? $item['created_at'] : '';
                $date = DateTimeUtils::chartDateFromStorageDateTime($createdAt);
                $raw = isset($item[$reqField]) && is_numeric($item[$reqField]) ? (float) $item[$reqField] : 0.0;

                $formatted = match ($reqField) {
                    'req_time_median', 'req_time_p95' => number_format($raw * 1000, 3, '.', ''),
                    default => number_format($raw, 3, '.', ''),
                };

                if (!isset($result[$date])) {
                    $result[$date] = [];
                }

                $result[$date][$reqField] = $formatted;
            }

            $timers[] = $reqField;
        }

        $isServerFilter = $serverFilter ? 'server,' : '';

        $aggregationAgg = $aggregation[$valueField]['agg'];

        $sql = "
            SELECT
                category, $isServerFilter $dateSelect as created_at, $aggregationAgg as $valueField
            FROM
                ipm_tag_info
            WHERE
                server_name = :server_name
                $hostCondition
                $serverCondition
                AND `category` IS NOT NULL
                AND created_at > :created_at
            GROUP BY
                category, $isServerFilter $timeGroupBy
            ORDER BY
                created_at ASC, $valueField DESC
        ";

        $data = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        foreach ($data as $item) {
            $createdAt = is_string($item['created_at']) ? $item['created_at'] : '';
            $date = DateTimeUtils::chartDateFromStorageDateTime($createdAt);

            if (isset($item['timer_median']) && is_numeric($item['timer_median'])) {
                $item['timer_median'] = number_format((float) $item['timer_median'] * 1000, 3, '.', '');
            }
            if (isset($item['timer_p95']) && is_numeric($item['timer_p95'])) {
                $item['timer_p95'] = number_format((float) $item['timer_p95'] * 1000, 3, '.', '');
            }
            if (isset($item['timer_value']) && is_numeric($item['timer_value'])) {
                $item['timer_value'] = number_format((float) $item['timer_value'], 3, '.', '');
            }
            if (isset($item['hit_count']) && is_numeric($item['hit_count'])) {
                $item['hit_count'] = number_format((float) $item['hit_count'], 0, '.', '');
            }

            $category = is_string($item['category']) ? $item['category'] : '';
            $server = is_string($item['server'] ?? null) ? $item['server'] : '';
            $key = $category;
            if ($serverFilter && strlen($server)) {
                $key .= " ($server)";
            }

            if (!in_array($key, $timers)) {
                $timers[] = $key;
            }

            if (!isset($result[$date])) {
                $result[$date] = [];
            }
            $result[$date][$key] = $item[$valueField];
        }

        return [
            'timers' => !empty($data) ? $timers : [],
            'values' => !empty($data) ? $result : []
        ];
    }

    /** @return list<array<string, mixed>> */
    private function getErrorPages(string $serverName, string $hostName, int $startPos, int $rowCount, ?string $colOrder, string $colDir): array
    {
        $params = [
            'server_name' => $serverName,
            'created_at' => DateTimeUtils::storageDateTimeAgo('1 week')
        ];

        $hostCondition = '';
        if ($hostName !== 'all') {
            $params['hostname'] = $hostName;
            $hostCondition = 'AND hostname = :hostname';
        }

        $orderBy = 'created_at DESC';
        if ($colOrder !== null) {
            $orderBy = $this->generateOrderBy($colOrder, $colDir, 'ipm_status_details');
        }

        $sql = "
            SELECT
                DISTINCT server_name,
                         hostname,
                         script_name,
                         status,
                         tags,
                         tags_cnt,
                         created_at
            FROM
                ipm_status_details
            WHERE
                server_name = :server_name
                $hostCondition
                AND created_at > :created_at
            ORDER BY
                $orderBy
            LIMIT
                $startPos, $rowCount
        ";

        $data = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        $rows = [];
        foreach ($data as $item) {
            $createdAt = is_string($item['created_at']) ? $item['created_at'] : '';
            $item['created_at_format'] = DateTimeUtils::formatStorageDateTimeForServer($createdAt);
            $item['script_name'] = Utils::urlDecode(is_string($item['script_name']) ? $item['script_name'] : '');
            $parsed = Utils::parseRequestTags($item);
            $rows[] = is_array($parsed) ? $parsed : $item;
        }

        return $rows;
    }

    private function getSlowPagesCount(string $serverName, string $hostName): int
    {
        $params = [
            'server_name' => $serverName,
            'created_at' => DateTimeUtils::storageDateTimeAgo('1 day')
        ];

        $hostCondition = '';
        if ($hostName !== 'all') {
            $params['hostname'] = $hostName;
            $hostCondition = 'AND hostname = :hostname';
        }

        $sql = "
            SELECT
                COUNT(DISTINCT request_id)
            FROM
                ipm_req_time_details
            WHERE
                server_name = :server_name
                $hostCondition
                AND created_at > :created_at
        ";

        $count = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchOne();

        return is_numeric($count) ? (int) $count : 0;
    }

    /** @return list<array<string, mixed>> */
    private function getSlowPages(string $serverName, string $hostName, int $startPos, int $rowCount, ?string $colOrder, string $colDir): array
    {
        $params = [
            'server_name' => $serverName,
            'created_at' => DateTimeUtils::storageDateTimeAgo('1 day')
        ];

        $hostCondition = '';
        if ($hostName !== 'all') {
            $params['hostname'] = $hostName;
            $hostCondition = 'AND hostname = :hostname';
        }

        $orderBy = 'created_at DESC, req_time DESC';
        if ($colOrder !== null) {
            $orderBy = $this->generateOrderBy($colOrder, $colDir, 'ipm_req_time_details');
        }

        $sql = "
            SELECT
                DISTINCT request_id, server_name, hostname, script_name, req_time, tags, tags_cnt, timers_cnt, created_at
            FROM
                ipm_req_time_details
            WHERE
                server_name = :server_name
                $hostCondition
                AND created_at > :created_at
            ORDER BY
                $orderBy
            LIMIT
                $startPos, $rowCount
        ";

        $data = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        $rows = [];
        foreach ($data as $item) {
            $createdAt = is_string($item['created_at']) ? $item['created_at'] : '';
            $item['created_at_format'] = DateTimeUtils::formatStorageDateTimeForServer($createdAt);
            $item['script_name'] = Utils::urlDecode(is_string($item['script_name']) ? $item['script_name'] : '');
            $item['req_time'] = number_format(is_numeric($item['req_time']) ? (float) $item['req_time'] * 1000 : 0.0, 0, '.', ',');
            $parsed = Utils::parseRequestTags($item);
            $rows[] = is_array($parsed) ? $parsed : $item;
        }

        return $rows;
    }

    private function getHeavyPagesCount(string $serverName, string $hostName): int
    {
        $params = [
            'server_name' => $serverName,
            'created_at' => DateTimeUtils::storageDateTimeAgo('1 day')
        ];

        $hostCondition = '';
        if ($hostName !== 'all') {
            $params['hostname'] = $hostName;
            $hostCondition = 'AND hostname = :hostname';
        }

        $sql = "
            SELECT
                COUNT(DISTINCT server_name, hostname, script_name, mem_peak_usage, tags, tags_cnt, created_at) as cnt
            FROM
                ipm_mem_peak_usage_details
            WHERE
                server_name = :server_name
                $hostCondition
                AND created_at > :created_at
        ";

        $count = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchOne();

        return is_numeric($count) ? (int) $count : 0;
    }

    private function getCPUPagesCount(string $serverName, string $hostName): int
    {
        $params = [
            'server_name' => $serverName,
            'created_at' => DateTimeUtils::storageDateTimeAgo('1 day')
        ];
        $hostCondition = '';

        if ($hostName !== 'all') {
            $params['hostname'] = $hostName;
            $hostCondition = 'AND hostname = :hostname';
        }

        $sql = "
            SELECT
                COUNT(DISTINCT server_name, hostname, script_name, cpu_peak_usage, tags, tags_cnt, created_at) as cnt
            FROM
                ipm_cpu_usage_details
            WHERE
                server_name = :server_name
                $hostCondition
                AND created_at > :created_at
        ";

        $count = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchOne();

        return is_numeric($count) ? (int) $count : 0;
    }

    /** @return list<array<string, mixed>> */
    private function getCPUPages(string $serverName, string $hostName, int $startPos, int $rowCount, ?string $colOrder, string $colDir): array
    {
        $params = [
            'server_name' => $serverName,
            'created_at' => DateTimeUtils::storageDateTimeAgo('1 day')
        ];

        $hostCondition = '';
        if ($hostName !== 'all') {
            $params['hostname'] = $hostName;
            $hostCondition = 'AND hostname = :hostname';
        }

        $orderBy = 'created_at DESC, cpu_peak_usage DESC';
        if ($colOrder !== null) {
            $orderBy = $this->generateOrderBy($colOrder, $colDir, 'ipm_cpu_usage_details');
        }

        $sql = "
            SELECT
                DISTINCT server_name, hostname, script_name, cpu_peak_usage, tags, tags_cnt, created_at
            FROM
                ipm_cpu_usage_details
            WHERE
                server_name = :server_name
                $hostCondition
                AND created_at > :created_at
            ORDER BY
                $orderBy
            LIMIT
                $startPos, $rowCount
        ";

        $data = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        $rows = [];
        foreach ($data as $item) {
            $createdAt = is_string($item['created_at']) ? $item['created_at'] : '';
            $item['created_at_format'] = DateTimeUtils::formatStorageDateTimeForServer($createdAt);
            $item['script_name'] = Utils::urlDecode(is_string($item['script_name']) ? $item['script_name'] : '');
            $item['cpu_peak_usage'] = number_format(is_numeric($item['cpu_peak_usage']) ? (float) $item['cpu_peak_usage'] : 0.0, 3);
            $parsed = Utils::parseRequestTags($item);
            $rows[] = is_array($parsed) ? $parsed : $item;
        }

        return $rows;
    }

    /** @return list<array<string, mixed>> */
    private function getHeavyPages(string $serverName, string $hostName, int $startPos, int $rowCount, ?string $colOrder, string $colDir): array
    {
        $params = [
            'server_name' => $serverName,
            'created_at' => DateTimeUtils::storageDateTimeAgo('1 day')
        ];

        $hostCondition = '';
        if ($hostName !== 'all') {
            $params['hostname'] = $hostName;
            $hostCondition = 'AND hostname = :hostname';
        }

        $orderBy = 'created_at DESC, mem_peak_usage DESC';
        if ($colOrder !== null) {
            $orderBy = $this->generateOrderBy($colOrder, $colDir, 'ipm_mem_peak_usage_details');
        }

        $sql = "
            SELECT
                DISTINCT server_name,
                         hostname,
                         script_name,
                         mem_peak_usage,
                         tags,
                         tags_cnt,
                         created_at
            FROM
                ipm_mem_peak_usage_details
            WHERE
                server_name = :server_name
                $hostCondition
                AND created_at > :created_at
            ORDER BY
                $orderBy
            LIMIT
                $startPos, $rowCount
        ";

        $data = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        $rows = [];
        foreach ($data as $item) {
            $createdAt = is_string($item['created_at']) ? $item['created_at'] : '';
            $item['created_at_format'] = DateTimeUtils::formatStorageDateTimeForServer($createdAt);
            $item['script_name'] = Utils::urlDecode(is_string($item['script_name']) ? $item['script_name'] : '');
            $item['mem_peak_usage'] = number_format(is_numeric($item['mem_peak_usage']) ? (float) $item['mem_peak_usage'] : 0.0, 0, '.', ',');
            $parsed = Utils::parseRequestTags($item);
            $rows[] = is_array($parsed) ? $parsed : $item;
        }

        return $rows;
    }

    /**
     * @param list<string|int> $ids
     * @return list<array<string, mixed>>
     */
    private function getTagsTimersForIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $ids = implode(', ', $ids);

        $sql = "
            SELECT
                id, tags, tags_cnt, timers_cnt
            FROM
                request
            WHERE
                id IN ($ids)
        ";

        return $this->entityManager->getConnection()->executeQuery($sql)->fetchAllAssociative();
    }


    /**
     * @param array<string, mixed> $filter
     * @return list<array<string, mixed>>
     */
    private function getLivePages(string $serverName, string $hostName, array $filter, int $limit = 50): array
    {
        $params = [
            'server_name' => $serverName
        ];

        $hostCondition = '';
        $idCondition = '';

        if ($hostName !== 'all') {
            $params['hostname'] = $hostName;
            $hostCondition .= ' AND hostname = :hostname';
        }

        $lastId = isset($filter['last_id']) && is_numeric($filter['last_id']) ? (int) $filter['last_id'] : 0;
        $lastTimestamp = isset($filter['last_timestamp']) && is_numeric($filter['last_timestamp']) ? (int) $filter['last_timestamp'] : 0;
        if ($lastId > 0) {
            $params['last_id'] = $lastId;
            $params['last_timestamp'] = $lastTimestamp;
            $idCondition .= ' AND id <> :last_id AND timestamp >= :last_timestamp';
        }

        if (isset($filter['req_time']) && is_numeric($filter['req_time']) && (float) $filter['req_time'] > 0) {
            $params['req_time'] = (float) $filter['req_time'] / 1000;
            $idCondition .= ' AND req_time >= :req_time';
        }

        $scriptName = isset($filter['script_name']) && is_string($filter['script_name']) ? $filter['script_name'] : '';
        if ($scriptName !== '') {
            $params['script_name'] = $scriptName . '%';
            $idCondition .= ' AND script_name LIKE :script_name';
        }

        $sql = "
            SELECT
                id,
                server_name,
                hostname,
                script_name,
                req_time,
                status,
                mem_peak_usage,
                ru_utime,
                timestamp
            FROM
                request
            WHERE
                server_name = :server_name
                $hostCondition
                $idCondition
            ORDER BY
                timestamp DESC, id DESC
            LIMIT
                $limit
        ";

        $rows = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        $data = [];
        foreach ($rows as $item) {
            $itemId = is_numeric($item['id']) ? (int) $item['id'] : 0;
            $itemTimestamp = is_numeric($item['timestamp']) ? (int) $item['timestamp'] : 0;
            if ($lastId > 0) {
                if ($itemTimestamp === $lastTimestamp && $itemId >= $lastId - 10000) {
                    continue;
                }
            }

            $reqTime = is_numeric($item['req_time']) ? (float) $item['req_time'] * 1000 : 0.0;
            $item['script_name'] = Utils::urlDecode(is_string($item['script_name']) ? $item['script_name'] : '');
            $item['req_time'] = $reqTime;
            $item['req_time_format'] = number_format($reqTime);
            $item['mem_peak_usage_format'] = number_format(is_numeric($item['mem_peak_usage']) ? (float) $item['mem_peak_usage'] : 0.0);
            $item['timestamp_format'] = DateTimeUtils::formatUnixTimestampForServer($itemTimestamp);
            $data[] = $item;
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function loadServerFilter(mixed $raw, string $serverName): array
    {
        if (!is_array($raw)) {
            return [];
        }
        $entry = $raw[$serverName] ?? [];
        if (!is_array($entry)) {
            return [];
        }
        $result = [];
        foreach ($entry as $k => $v) {
            if (is_string($k)) {
                $result[$k] = $v;
            }
        }
        return $result;
    }

    /** @return array<string, array<string, mixed>> */
    private function loadAllFilters(mixed $raw): array
    {
        if (!is_array($raw)) {
            return [];
        }
        $result = [];
        foreach ($raw as $serverName => $filter) {
            if (is_string($serverName) && is_array($filter)) {
                $serverResult = [];
                foreach ($filter as $k => $v) {
                    if (is_string($k)) {
                        $serverResult[$k] = $v;
                    }
                }
                $result[$serverName] = $serverResult;
            }
        }
        return $result;
    }

    private function generateOrderBy(?string $colOrder, string $colDir, string $table): string
    {
        $orderBy = 'created_at DESC';

        if ($colOrder !== null) {
            if ($colDir === 'asc') {
                $dir = 'ASC';
            } else {
                $dir = 'DESC';
            }

            if ($table === 'ipm_req_time_details' && $colOrder === 'time') {
                $orderBy = "req_time $dir, created_at DESC";
            } elseif ($table === 'ipm_mem_peak_usage_details' && $colOrder === 'mem') {
                $orderBy = "mem_peak_usage $dir, created_at DESC";
            } elseif ($table === 'ipm_cpu_usage_details' && $colOrder === 'cpu') {
                $orderBy = "cpu_peak_usage $dir, created_at DESC";
            } else {
                switch ($colOrder) {
                    case 'host':
                        $orderBy = "hostname $dir, created_at DESC";
                        break;
                    case 'script':
                        $orderBy = "script_name $dir, created_at DESC";
                        break;
                    default:
                        $orderBy = "created_at $dir";
                        break;
                }
            }
        }

        return $orderBy;
    }

    private function getErrorPagesCount(string $serverName, string $hostName): int
    {
        $params = [
            'server_name' => $serverName,
            'created_at'  => DateTimeUtils::storageDateTimeAgo('1 week'),
        ];
        $hostCondition = '';

        if ($hostName !== 'all') {
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

        $count = $this->entityManager->getConnection()->executeQuery($sql, $params)->fetchOne();

        return is_numeric($count) ? (int) $count : 0;
    }
}
