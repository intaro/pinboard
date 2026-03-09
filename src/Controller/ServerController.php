<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use App\Command\AggregateCommand;
use App\Utils\SqlUtils;
use App\Utils\Utils;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ServerController extends AbstractController
{
    private const ROW_PER_PAGE = 50;

    private int $rowPerPage;

//    private $server = $app['controllers_factory'];
    private $allowedPeriods = ['1 day', '3 days', '1 week', '1 month'];

    private EntityManagerInterface $entityManager;
    function __construct(
        EntityManagerInterface $entityManager,
        #[Autowire('%env(int:APP_PAGINATION_ROW_PER_PAGE)%')] int $rowPerPage
    )
    {
        $this->entityManager = $entityManager;
        $this->rowPerPage = $rowPerPage > 0 ? $rowPerPage : self::ROW_PER_PAGE;

//        $product = $entityManager->getRepository(Product::class)->find($id);
//        return new Response('Check out this great product: '.$product->getName());
    }

    #[Route('/{serverName}/{hostName}/overview.{format}', name: 'server', methods: ['GET'])]
    public function actionOverview(Request $request, $serverName, $hostName, $format): Response
    {
        $period = $request->get('period', '1 day');
        if (!in_array($period, $this->allowedPeriods)) {
            $period = '1 day';
        }

        $result = [];
        $result['hosts'] = $this->getHosts($this->entityManager, $serverName);
        $result['req'] = $this->getRequestReview($this->entityManager, $serverName, $hostName, $period);
        $result['req_per_sec'] = $this->getRequestPerSecReview($this->entityManager, $serverName, $hostName, $period);
        $result['statuses'] = $this->getStatusesReview($this->entityManager, $serverName, $hostName, $period);

        if ($format === 'html') {
            $result['server_name'] = $serverName;
            $result['hostname'] = $hostName;
            $result['period'] = $period;
            $result['periods'] = $this->allowedPeriods;
            $result['title'] = $serverName;
            $result['req_time_border'] = number_format($this->getReqTimeBorder($this, $serverName) * 1000, 0, '.', '');
            $result['menu'] = $this->buildMenu();

//            Для теста
            $result['base_url'] = '/';

            return $this->render('server.html.twig', $result);
        }

        if ($format === 'json') {
            unset($result['hosts']);

            foreach ($result['req'] as &$value) {
                unset($value['date']);
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
                    unset($value['parsed_hostname']);
                }
            }

            foreach ($result['statuses']['data'] as &$value) {
                unset($value['date']);
            }

            $result['success'] = 'true';

            $response = new JsonResponse($result);
            $response->setStatusCode(200);

            return $response;
        }
    }

    #[Route('/{serverName}/{hostName}/timers', name: 'server_timers',  methods: ['GET'])]
    public function actionTimers(Request $request, $serverName, $hostName)
    {
        $period = $request->get('period', '1 day');
        if (!in_array($period, $this->allowedPeriods)) {
            $period = '1 day';
        }

        $serverFilter = $request->get('server', 'off');
        if (!in_array($serverFilter, ['on', 'off'])) {
            $serverFilter = 'off';
        }
        $serverFilter = $serverFilter === 'on';

        $result = [
            'hosts' => $this->getHosts($this->entityManager, $serverName),
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
                    'data' => $this->getTimersList($this->entityManager, $serverName, $hostName, 'hit_count', $period, $serverFilter),
                ],
                'timer_value' => [
                    'title' => 'Timer value',
                    'subtitle' => 'total',
                    'field' => 'timer_value',
                    'unit' => ' s',
                    'data' => $this->getTimersList($this->entityManager, $serverName, $hostName, 'timer_value', $period, $serverFilter),
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

        //            Для теста
        $result['base_url'] = '/';
        $result['menu'] = $this->buildMenu();

        return $this->render('timers.html.twig', $result);
    }

    #[Route('/{serverName}/{hostName}/statuses/{pageNum}/{colOrder}/{colDir}', name: 'server_statuses',  methods: ['GET'])]
    public function actionStatuses(Request $request, $serverName, $hostName, $pageNum, $colOrder, $colDir)
    {
        $pageNum = str_replace('page', '', $pageNum);

        $result = [
            'server_name' => $serverName,
            'hostname' => $hostName,
            'title' => "Error pages / $serverName",
            'pageNum' => $pageNum,
            'colOrder' => $colOrder,
            'colDir' => $colDir
        ];

        $result['rowPerPage'] = $this->rowPerPage;

        $rowCount = $this->getErrorPagesCount($this->entityManager, $serverName, $hostName);
        $result['rowCount'] = $rowCount;

        $pageCount = ceil($rowCount / $this->rowPerPage);
        $result['pageCount'] = $pageCount;

        if ((int)$pageCount !== 0) {
            if ($pageNum < 1 || $pageNum > $pageCount) {
                throw $this->createNotFoundException("Page $pageNum does not exist.");
            }
        }

        $startPos = ($pageNum - 1) * $this->rowPerPage;
        $result['hosts'] = $this->getHosts($this->entityManager, $serverName);
        $result['statuses'] = $this->getErrorPages($this->entityManager, $serverName, $hostName, $startPos, $this->rowPerPage, $colOrder, $colDir);

        //            Для теста
        $result['base_url'] = '/';
        $result['menu'] = $this->buildMenu();

        return $this->render('statuses.html.twig', $result);
    }

    #[Route('/{serverName}/{hostName}/req-time/{pageNum}/{colOrder}/{colDir}', name: 'server_req_time',  methods: ['GET'])]
    public function actionReqTime(Request $request, $serverName, $hostName, $pageNum, $colOrder, $colDir)
    {
        $pageNum = str_replace('page', '', $pageNum);

        $result = [
            'server_name' => $serverName,
            'hostname' => $hostName,
            'title' => "Request time / $serverName",
            'pageNum' => $pageNum,
            'colOrder' => $colOrder,
            'colDir' => $colDir
        ];

        $result['rowPerPage'] = $this->rowPerPage;

        $rowCount = $this->getSlowPagesCount($this->entityManager, $serverName, $hostName);
        $result['rowCount'] = $rowCount;

        $pageCount = ceil($rowCount / $this->rowPerPage);
        $result['pageCount'] = $pageCount;
        if ((int)$pageCount !== 0) {
            if ($pageNum < 1 || $pageNum > $pageCount) {
                throw $this->createNotFoundException("Page $pageNum does not exist.");
            }
        }
        $startPos = ($pageNum - 1) * $this->rowPerPage;

        $result['hosts'] = $this->getHosts($this->entityManager, $serverName);
        $result['pages'] = $this->getSlowPages($this->entityManager, $serverName, $hostName, $startPos, $this->rowPerPage, $colOrder, $colDir);

        //            Для теста
        $result['base_url'] = '/';
        $result['menu'] = $this->buildMenu();

        return $this->render('req_time.html.twig', $result);
    }

    #[Route('/{serverName}/{hostName}/mem-usage/{pageNum}/{colOrder}/{colDir}', name: 'server_mem_usage',  methods: ['GET'])]
    public function actionMemUsage(Request $request, $serverName, $hostName, $pageNum, $colOrder, $colDir)
    {
        $pageNum = str_replace('page', '', $pageNum);

        $result = [
            'server_name' => $serverName,
            'hostname' => $hostName,
            'title' => "Memory peak usage / $serverName",
            'pageNum' => $pageNum,
            'colOrder' => $colOrder,
            'colDir' => $colDir
        ];

        $result['rowPerPage'] = $this->rowPerPage;

        $rowCount = $this->getHeavyPagesCount($this->entityManager, $serverName, $hostName);
        $result['rowCount'] = $rowCount;

        $pageCount = ceil($rowCount / $this->rowPerPage);
        $result['pageCount'] = $pageCount;
        if ((int)$pageCount !== 0) {
            if ($pageNum < 1 || $pageNum > $pageCount) {
                throw $this->createNotFoundException("Page $pageNum does not exist.");
            }
        }
        $startPos = ($pageNum - 1) * $this->rowPerPage;

        $result['hosts'] = $this->getHosts($this->entityManager, $serverName);
        $result['pages'] = $this->getHeavyPages($this->entityManager, $serverName, $hostName, $startPos, $this->rowPerPage, $colOrder, $colDir);

        //            Для теста
        $result['base_url'] = '/';
        $result['menu'] = $this->buildMenu();

        return $this->render('mem_usage.html.twig', $result);
    }

    #[Route('/{serverName}/{hostName}/cpu-usage/{pageNum}/{colOrder}/{colDir}', name: 'server_cpu_usage',  methods: ['GET'])]
    public function actionCpuUsage(Request $request, $serverName, $hostName, $pageNum, $colOrder, $colDir)
    {
        $pageNum = str_replace('page', '', $pageNum);

        $result = [
            'server_name' => $serverName,
            'hostname' => $hostName,
            'title' => "CPU peak usage / $serverName",
            'pageNum' => $pageNum,
            'colOrder' => $colOrder,
            'colDir' => $colDir
        ];

        $result['rowPerPage'] = $this->rowPerPage;

        $rowCount = $this->getCPUPagesCount($this->entityManager, $serverName, $hostName);
        $result['rowCount'] = $rowCount;

        $pageCount = ceil($rowCount / $this->rowPerPage);
        $result['pageCount'] = $pageCount;
        if ((int)$pageCount !== 0) {
            if ($pageNum < 1 || $pageNum > $pageCount) {
                throw $this->createNotFoundException("Page $pageNum does not exist.");
            }
        }
        $startPos = ($pageNum - 1) * $this->rowPerPage;

        $result['hosts'] = $this->getHosts($this->entityManager, $serverName);
        $result['pages'] = $this->getCPUPages($this->entityManager, $serverName, $hostName, $startPos, $this->rowPerPage, $colOrder, $colDir);

        //            Для теста
        $result['base_url'] = '/';
        $result['menu'] = $this->buildMenu();

        return $this->render('cpu_usage.html.twig', $result);
    }

    #[Route('/{serverName}/{hostName}/live', name: 'server_live', methods: ['GET', 'POST'])]
    public function actionLive(Request $request, $serverName, $hostName)
    {
        $session = $request->hasSession() ? $request->getSession() : null;

        // filter from session
        $liveFilter = $session?->get('filter_params');
        if (!$liveFilter) {
            $liveFilter = [];
        }
        if (!isset($liveFilter[$serverName])) {
            $liveFilter[$serverName] = [];
        }

        $result = [
            'server_name' => $serverName,
            'hostname' => $hostName,
            'title' => "Live / $serverName",
            'limit' => 100
        ];

        if ($request->isXmlHttpRequest()) {
            $result['limit'] = 50;

            // save filter in session
            $liveFilter[$serverName]['req_time'] = $request->get('req_time');
            $liveFilter[$serverName]['tags'] = $request->get('tags');

            $session?->set('filter_params', $liveFilter);

            $liveFilter[$serverName]['last_id'] = $request->get('last_id');
            $liveFilter[$serverName]['last_timestamp'] = $request->get('last_timestamp');
        } else {
            $result['filter'] = $liveFilter[$serverName];
            $result['show_filter'] = false;

            if (count($result['filter'])) {
                foreach ($result['filter'] as $item) {
                    if ($item) {
                        $result['show_filter'] = true;

                        break;
                    }
                }
            }
        }

        $result['pages'] = $this->getLivePages($this->entityManager, $serverName, $hostName, $liveFilter[$serverName], $result['limit']);

        $ids = [];
        foreach ($result['pages'] as $item) {
            $ids[] = $item['id'];
        }
        $addData = $this->getTagsTimersForIds($this->entityManager, $ids);

        $tagsFilter = [];
        if (isset($liveFilter[$serverName]['tags'])) {
            if (preg_match_all(
                '/([\w\:-_]+)\s*?\=\s*?([\w\:-_]+)/',
                $liveFilter[$serverName]['tags'],
                $matches,
                PREG_SET_ORDER
            )
            ) {
                foreach ($matches as $match) {
                    $tagsFilter[$match[1]] = $match[2];
                }
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
            $result['hosts'] = $this->getHosts($this->entityManager, $serverName);
            $result['last_id'] = count($result['pages']) ? $result['pages'][0]['id'] : 0;
            $result['last_timestamp'] = count($result['pages']) ? $result['pages'][0]['timestamp'] : 0;
            $result['menu'] = $this->buildMenu();

            //            Для теста
            $result['base_url'] = '/';

            $response = $this->render('live.html.twig', $result);

            $response->headers->addCacheControlDirective('no-cache', true);
            $response->headers->addCacheControlDirective('no-store', true);
            $response->headers->addCacheControlDirective('must-revalidate', true);
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');

            return $response;
        }
    }


    // Нужно переосмыслить метод полносьтю, пока для теста
    private function buildMenu(): array
    {
        return (new BeforeController($this->entityManager))->actionBefore();
    }

    // Нужно переосмыслить метод полносьтю, пока для теста
    function getReqTimeBorder($app, $serverName)
    {
//        if (isset($app['params']['notification']['border']['req_time'][$serverName])) {
//            return $app['params']['notification']['border']['req_time'][$serverName];
//        }
//
//        if (isset($app['params']['notification']['border']['req_time']['global'])) {
//            return $app['params']['notification']['border']['req_time']['global'];
//        }

        return AggregateCommand::DEFAULT_REQ_TIME_BORDER;
    }

    function getHosts($conn, $serverName)
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

//        $stmt = $conn->executeQuery($sql, ['server_name' => $serverName]);
        $hosts = $conn->getConnection()->executeQuery($sql, ['server_name' => $serverName])->fetchAllAssociative();

//        $hosts = [];
//        while ($data = $stmt->fetch()) {
//            $hosts[] = $data['hostname'];
//        }

        return array_map(static fn (array $row): string => (string) $row['hostname'], $hosts);
    }

    function getStatusesReview($conn, $serverName, $hostName, $period)
    {
        $dateSelect = SqlUtils::getDateSelectExpression($period);
        $params = [
            'server_name' => $serverName,
            'created_at' => date('Y-m-d H:i:s', strtotime('-' . $period))
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

//        $stmt = $conn->executeQuery($sql, $params);
        $stmt = $conn->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        $statuses = [
            'data' => [],
            'codes' => []
        ];

        foreach ($stmt as $data) {
            $t = strtotime($data['created_at']);
            $date = date('Y,', $t) . (date('n', $t) - 1) . date(',d,H,i', $t);

            $statuses['data'][] = [
                'created_at' => $data['created_at'],
                'date' => $date,
                'error_code' => $data['status'],
                'error_count' => $data['cnt']
            ];

            if (!isset($statuses['codes'][$data['status']])) {
                // Set color
                $statuses['codes'][$data['status']] = Utils::generateColor();
            }
        }
        ksort($statuses['codes']);

        return $statuses;
    }

    function getRequestPerSecReview($conn, $serverName, $hostName, $period)
    {
        $dateSelect = SqlUtils::getDateSelectExpression($period);
        $params = [
            'server_name' => $serverName,
            'created_at' => date('Y-m-d H:i:s', strtotime('-' . $period))
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

//        $data = $conn->fetchAll($sql, $params);
        $data = $conn->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        $rpqData = [
            'data' => [],
            'hosts' => []
        ];
        $hostCount = 0;

        foreach ($data as &$item) {
            $t = strtotime($item['created_at']);
            $date = date('Y,', $t) . (date('n', $t) - 1) . date(',d,H,i', $t);
            $parsedHostname = '_' . preg_replace('/\W/', '_', $item['hostname']);

            $rpqData['data'][$date][] = [
                'created_at' => $item['created_at'],
                // 'date' => $date,
                'hostname' => $item['hostname'],
                'parsed_hostname' => $parsedHostname,
                'req_per_sec' => number_format($item['req_per_sec'], 2, '.', '')
            ];

            if (!isset($rpqData['hosts'][$parsedHostname])) {
                $rpqData['hosts'][$parsedHostname]['color'] = Utils::generateColor();
                $rpqData['hosts'][$parsedHostname]['host'] = $item['hostname'];
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

            $data = $conn->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();
            $rpqData['hosts']['_']['color'] = Utils::generateColor();
            $rpqData['hosts']['_']['host'] = '_';

            foreach ($data as &$item) {
                $t = strtotime($item['created_at']);
                $date = date('Y,', $t) . (date('n', $t) - 1) . date(',d,H,i', $t);

                $rpqData['data'][$date][] = [
                    'created_at' => $item['created_at'],
                    //'date' => $date,
                    'hostname' => '_',
                    'parsed_hostname' => '_',
                    'req_per_sec' => number_format($item['req_per_sec'], 2, '.', '')
                ];
            }
        }

        ksort($rpqData['hosts']);

        return $rpqData;
    }

    function getRequestReview($conn, $serverName, $hostName, $period)
    {
        $dateSelect = SqlUtils::getDateSelectExpression($period);
        $params = [
            'server_name' => $serverName,
            'created_at' => date('Y-m-d H:i:s', strtotime('-' . $period)),
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

//        $data = $conn->fetchAll($sql, $params);
        $data = $conn->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        foreach ($data as &$item) {
            $t = strtotime($item['created_at']);
            $item['date'] = date('Y,', $t) . (date('n', $t) - 1) . date(',d,H,i', $t);

            foreach (['90', '95', '99', '100'] as $percent) {
                $item['req_time_' . $percent] = number_format($item['req_time_' . $percent] * 1000, 0, '.', '');
            }
            foreach (['90', '95', '99', '100'] as $percent) {
                $item['mem_peak_usage_' . $percent] = number_format($item['mem_peak_usage_' . $percent], 0, '.', '');
            }
            foreach (['90', '95', '99', '100'] as $percent) {
                $item['cpu_peak_usage_' . $percent] = number_format($item['cpu_peak_usage_' . $percent], 3, '.', ',');
            }
        }

        return $data;
    }

    function getTimersList($conn, $serverName, $hostName, $valueField, $period, $serverFilter)
    {
        $params = [
            'server_name' => $serverName,
            'created_at' => date('Y-m-d H:i:s', strtotime('-' . $period))
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

            $data = $conn->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

            foreach ($data as $item) {
                $t = strtotime($item['created_at']);
                $item['date'] = date('Y,', $t) . (date('n', $t) - 1) . date(',d,H,i', $t);

                if (isset($item['req_time_median'])) {
                    $item['req_time_median'] = number_format($item['req_time_median'] * 1000, 3, '.', '');
                }
                if (isset($item['req_time_p95'])) {
                    $item['req_time_p95'] = number_format($item['req_time_p95'] * 1000, 3, '.', '');
                }
                if (isset($item['req_time_total'])) {
                    $item['req_time_total'] = number_format($item['req_time_total'], 3, '.', '');
                }

                if (!isset($result[$item['date']])) {
                    $result[$item['date']] = [];
                }

                $result[$item['date']][$aggregation[$valueField]['req_field']] = $item[$aggregation[$valueField]['req_field']];
            }

            $timers[] = $aggregation[$valueField]['req_field'];
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

        $data = $conn->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        foreach ($data as &$item) {
            $t = strtotime($item['created_at']);
            $item['date'] = date('Y,', $t) . (date('n', $t) - 1) . date(',d,H,i', $t);

            if (isset($item['timer_median'])) {
                $item['timer_median'] = number_format($item['timer_median'] * 1000, 3, '.', '');
            }
            if (isset($item['timer_p95'])) {
                $item['timer_p95'] = number_format($item['timer_p95'] * 1000, 3, '.', '');
            }
            if (isset($item['timer_value'])) {
                $item['timer_value'] = number_format($item['timer_value'], 3, '.', '');
            }
            if (isset($item['hit_count'])) {
                $item['hit_count'] = number_format($item['hit_count'], 0, '.', '');
            }

            $key = $item['category'];
            if ($serverFilter && strlen($item['server'])) {
                $key .= " ({$item['server']})";
            }

            if (!in_array($key, $timers)) {
                $timers[] = $key;
            }

            if (!isset($result[$item['date']])) {
                $result[$item['date']] = [];
            }
            $result[$item['date']][$key] = $item[$valueField];
        }

        return [
            'timers' => !empty($data) ? $timers : [],
            'values' => !empty($data) ? $result : []
        ];
    }

    function getErrorPages($conn, $serverName, $hostName, $startPos, $rowCount, $colOrder, $colDir)
    {
        $params = [
            'server_name' => $serverName,
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 week'))
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

        $data = $conn->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        foreach ($data as &$item) {
            $item['script_name'] = Utils::urlDecode($item['script_name']);
            $item = Utils::parseRequestTags($item);
        }

        return $data;
    }

    function getSlowPagesCount($conn, $serverName, $hostName)
    {
        $params = [
            'server_name' => $serverName,
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ];

        $hostCondition = '';
        if ($hostName !== 'all') {
            $params['hostname'] = $hostName;
            $hostCondition = 'AND hostname = :hostname';
        }

        $sql = "
            SELECT
                COUNT(*)
            FROM
                ipm_req_time_details
            WHERE
                server_name = :server_name
                $hostCondition
                AND created_at > :created_at
        ";

        $data = $conn->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        return (int)$data[0]['COUNT(*)'];
    }

    function getSlowPages($conn, $serverName, $hostName, $startPos, $rowCount, $colOrder, $colDir)
    {
        $params = [
            'server_name' => $serverName,
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
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

        $data = $conn->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        foreach ($data as &$item) {
            $item['script_name'] = Utils::urlDecode($item['script_name']);
            $item['req_time'] = number_format($item['req_time'] * 1000, 0, '.', ',');
            $item = Utils::parseRequestTags($item);
        }

        return $data;
    }

    function getHeavyPagesCount($conn, $serverName, $hostName)
    {
        $params = [
            'server_name' => $serverName,
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
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

        $data = $conn->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        return (int)$data[0]['cnt'];
    }

    function getCPUPagesCount($conn, $serverName, $hostName)
    {
        $params = [
            'server_name' => $serverName,
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
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

        $data = $conn->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        return (int)$data[0]['cnt'];
    }

    function getCPUPages($conn, $serverName, $hostName, $startPos, $rowCount, $colOrder, $colDir)
    {
        $params = [
            'server_name' => $serverName,
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
        ];

        $hostCondition = '';
        if ($hostName !== 'all') {
            $params['hostname'] = $hostName;
            $hostCondition = 'AND hostname = :hostname';
        }

        $orderBy = 'created_at DESC, cpu_peak_usage DESC';
        if (null !== $colOrder) {
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

        $data = $conn->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        foreach ($data as &$item) {
            $item['script_name'] = Utils::urlDecode($item['script_name']);
            $item['cpu_peak_usage'] = number_format($item['cpu_peak_usage'], 3);
            $item = Utils::parseRequestTags($item);
        }

        return $data;
    }

    function getHeavyPages($conn, $serverName, $hostName, $startPos, $rowCount, $colOrder, $colDir)
    {
        $params = [
            'server_name' => $serverName,
            'created_at' => date('Y-m-d H:i:s', strtotime('-1 day'))
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

        $data = $conn->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        foreach ($data as &$item) {
            $item['script_name'] = Utils::urlDecode($item['script_name']);
            $item['mem_peak_usage'] = number_format($item['mem_peak_usage'], 0, '.', ',');
            $item = Utils::parseRequestTags($item);
        }

        return $data;
    }

    function getTagsTimersForIds($conn, $ids)
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

        return $conn->fetchAll($sql);
    }


    function getLivePages($conn, $serverName, $hostName, array $filter, $limit = 50)
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

        if (isset($filter['last_id']) && $filter['last_id'] > 0) {
            $params['last_id'] = $filter['last_id'];
            $params['last_timestamp'] = $filter['last_timestamp'];
            $idCondition .= ' AND id <> :last_id AND timestamp >= :last_timestamp';
        }

        if (isset($filter['req_time']) && $filter['req_time']) {
            $params['req_time'] = $filter['req_time'] / 1000;
            $idCondition .= ' AND req_time >= :req_time';
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

        $data = $conn->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        foreach ($data as $k => &$item) {
            if (!empty($filter['last_id']) && $filter['last_id'] > 0) {
                if (
                    $item['timestamp'] === $filter['last_timestamp']
                    && $item['id'] >= $filter['last_id'] - 10000
                ) {
                    unset($data[$k]);

                    continue;
                }
            }

            $item['script_name'] = Utils::urlDecode($item['script_name']);
            $item['req_time'] = $item['req_time'] * 1000;
            $item['req_time_format'] = number_format($item['req_time']);
            $item['mem_peak_usage_format'] = number_format($item['mem_peak_usage']);
            $item['timestamp_format'] = date('H:i:s', $item['timestamp']);
        }

        return $data;
    }

    function generateOrderBy($colOrder, $colDir, $table)
    {
        $orderBy = 'created_at DESC';

        if ($colOrder !== null) {
            if ('asc' === $colDir) {
                $dir = 'ASC';
            } else {
                $dir = 'DESC';
            }

            if ('ipm_req_time_details' === $table && 'time' === $colOrder) {
                $orderBy = "req_time $dir, created_at DESC";
            } elseif ('ipm_mem_peak_usage_details' === $table && 'mem' === $colOrder) {
                $orderBy = "mem_peak_usage $dir, created_at DESC";
            } elseif ('ipm_cpu_usage_details' === $table && 'cpu' === $colOrder) {
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

    function getErrorPagesCount($conn, $serverName, $hostName) {
        $params = [
            'server_name' => $serverName,
            'created_at'  => date('Y-m-d H:i:s', strtotime('-1 week')),
        ];
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

        $data = $conn->getConnection()->executeQuery($sql, $params)->fetchAllAssociative();

        return (int)$data[0]['COUNT(*)'];
    }
}
