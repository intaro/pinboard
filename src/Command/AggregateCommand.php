<?php

declare(strict_types=1);

namespace App\Command;

use Doctrine\DBAL\Connection;
use Exception;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

#[AsCommand(name: 'aggregate', description: 'Aggregate data from source tables and save to report tables')]
class AggregateCommand extends Command
{
    /** @var array<string, mixed> */
    private array $params = [];
    private string $projectDir;

    public const DEFAULT_REQ_TIME_BORDER = 1.5;
    public const DEFAULT_SLOW_REQ_TIME = 1.5;
    public const DEFAULT_HEAVY_PAGE_MEMORY = 30000;
    public const DEFAULT_HEAVY_PAGE_CPU = 1;
    public const DEFAULT_LOCK_TTL_SECONDS = 900;

    public function __construct(
        private readonly Connection $db,
        private readonly Environment $twig,
        private readonly MailerInterface $mailer,
        KernelInterface $kernel
    ) {
        parent::__construct();
        $this->projectDir = $kernel->getProjectDir();
    }

    protected function configure(): void
    {
        $this->setDescription('Aggregate data from source tables and save to report tables');
    }

    /** @return array<string, mixed> */
    private function loadParams(): array
    {
        $longReqMap = $this->envJson('APP_LOGGING_LONG_REQUEST_TIME_MAP', []);
        $heavyReqMap = $this->envJson('APP_LOGGING_HEAVY_REQUEST_MAP', []);
        $heavyCpuMap = $this->envJson('APP_LOGGING_HEAVY_CPU_REQUEST_MAP', []);
        $notificationList = $this->envJson('APP_NOTIFICATION_LIST_JSON', []);
        $notificationBorderMap = $this->envJson('APP_NOTIFICATION_REQ_TIME_BORDER_MAP', []);
        $notificationIgnore = $this->envCsv('APP_NOTIFICATION_IGNORE');

        return [
            'records_lifetime' => $this->envString('APP_RECORDS_LIFETIME', 'P1M'),
            'aggregation_period' => $this->envString('APP_AGGREGATION_PERIOD', 'PT15M'),
            'logging' => [
                'long_request_time' => array_merge(
                    ['global' => (float)$this->envString('APP_LOGGING_LONG_REQUEST_TIME_GLOBAL', (string)static::DEFAULT_SLOW_REQ_TIME)],
                    is_array($longReqMap) ? $longReqMap : []
                ),
                'heavy_request' => array_merge(
                    ['global' => (float)$this->envString('APP_LOGGING_HEAVY_REQUEST_GLOBAL', (string)static::DEFAULT_HEAVY_PAGE_MEMORY)],
                    is_array($heavyReqMap) ? $heavyReqMap : []
                ),
                'heavy_cpu_request' => array_merge(
                    ['global' => (float)$this->envString('APP_LOGGING_HEAVY_CPU_REQUEST_GLOBAL', (string)static::DEFAULT_HEAVY_PAGE_CPU)],
                    is_array($heavyCpuMap) ? $heavyCpuMap : []
                ),
            ],
            'notification' => [
                'enable' => $this->envBool('APP_NOTIFICATION_ENABLE', false),
                'sender' => $this->envString('APP_NOTIFICATION_SENDER', 'noreply@pinboard'),
                'global_email' => $this->envString('APP_NOTIFICATION_GLOBAL_EMAIL', ''),
                'ignore' => $notificationIgnore,
                'list' => is_array($notificationList) ? $notificationList : [],
                'border' => [
                    'req_time' => array_merge(
                        ['global' => (float)$this->envString('APP_NOTIFICATION_REQ_TIME_BORDER_GLOBAL', (string)static::DEFAULT_REQ_TIME_BORDER)],
                        is_array($notificationBorderMap) ? $notificationBorderMap : []
                    ),
                ],
            ],
        ];
    }

    private function envString(string $name, string $default = ''): string
    {
        $value = $_ENV[$name] ?? $_SERVER[$name] ?? null;
        if (!is_string($value) || $value === '') {
            $value = getenv($name);
        }

        if ($value === false || $value === '') {
            return $default;
        }

        return $value;
    }

    private function envBool(string $name, bool $default = false): bool
    {
        $raw = $_ENV[$name] ?? $_SERVER[$name] ?? null;
        if (!is_string($raw) || $raw === '') {
            $raw = getenv($name);
        }

        if ($raw === false || $raw === '') {
            return $default;
        }

        $value = strtolower($raw);
        return in_array($value, ['1', 'true', 'yes', 'on'], true);
    }

    private function envInt(string $name, int $default): int
    {
        $raw = $this->envString($name, (string)$default);
        if (!is_numeric($raw)) {
            return $default;
        }

        $value = (int)$raw;
        return $value > 0 ? $value : $default;
    }

    /** @return list<string> */
    private function envCsv(string $name): array
    {
        $raw = $this->envString($name, '');
        if ($raw === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $raw))));
    }

    private function envJson(string $name, mixed $default): mixed
    {
        $raw = $this->envString($name, '');
        if ($raw === '') {
            return $default;
        }

        $decoded = json_decode($raw, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $default;
    }

    private function sender(): string
    {
        return $this->params['notification']['sender'] ?? 'noreply@pinboard';
    }

    /** @param string|list<string> $to */
    private function sendEmail(string|array $to, string $subject, string $html): void
    {
        $email = (new Email())
            ->from($this->sender())
            ->subject($subject)
            ->html($html);

        foreach ((array)$to as $recipient) {
            if (!empty($recipient)) {
                $email->addTo((string)$recipient);
            }
        }

        $this->mailer->send($email);
    }

    private function isNotIgnore(string $host): bool
    {
        $notIgnore = true;
        if (!empty($this->params['notification']['ignore'])) {
            foreach ($this->params['notification']['ignore'] as $hostToIgnore) {
                if (preg_match('#' . $hostToIgnore . '#', $host)) {
                    $notIgnore = false;
                    break;
                }
            }
        }

        return $notIgnore;
    }

    /**
     * @param array<string, list<array<string, mixed>>> $pages
     * @param string|list<string> $address
     */
    private function sendErrorPages(array $pages, string|array $address): void
    {
        if (count($pages) > 0) {
            $body = $this->twig->render('error_notification.html.twig', ['pages' => $pages]);
            $this->sendEmail($address, 'Intaro Pinboard found error pages', $body);
        }
    }

    /** @param list<array<string, mixed>> $errorPages */
    private function sendErrorEmails(array $errorPages): void
    {
        if (!empty($this->params['notification']['global_email'])) {
            $pages = [];
            foreach ($errorPages as $page) {
                if ($this->isNotIgnore($page['server_name'])) {
                    $pages[$page['server_name']][] = $page;
                }
            }
            $this->sendErrorPages($pages, $this->params['notification']['global_email']);
        }

        if (!empty($this->params['notification']['list'])) {
            foreach ($this->params['notification']['list'] as $item) {
                $pages = [];
                foreach ($errorPages as $page) {
                    if (preg_match('/' . $item['hosts'] . '/', $page['server_name']) && $this->isNotIgnore($page['server_name'])) {
                        $pages[$page['server_name']][] = $page;
                    }
                }
                $this->sendErrorPages($pages, $item['email']);
            }
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->params = $this->loadParams();

        $db = $this->db;

        try {
            $db->executeQuery('SELECT 1');
        } catch (\Throwable $e) {
            $output->writeln('<error>Can\'t connect to MySQL server</error>');

            return Command::FAILURE;
        }

        $lockFile = $this->projectDir . '/var/aggregate.lock';
        $lockTtlSeconds = $this->envInt('APP_AGGREGATE_LOCK_TTL_SECONDS', self::DEFAULT_LOCK_TTL_SECONDS);
        if (file_exists($lockFile)) {
            $mtime = @filemtime($lockFile);
            if ($mtime !== false && (time() - $mtime) > $lockTtlSeconds) {
                if (@unlink($lockFile)) {
                    $output->writeln(sprintf(
                        '<comment>Removed stale lock file %s (older than %d seconds).</comment>',
                        $lockFile,
                        $lockTtlSeconds
                    ));
                } else {
                    $output->writeln(sprintf(
                        '<error>Found stale lock file %s, but cannot remove it. Please remove it manually.</error>',
                        $lockFile
                    ));

                    return Command::FAILURE;
                }
            }
        }

        if (file_exists($lockFile)) {
            $output->writeln('<error>Cannot run data aggregation: another instance is already executing. Otherwise, remove ' . $lockFile . '</error>');

            if (!empty($this->params['notification']['global_email'])) {
                try {
                    $body = $this->twig->render('lock_notification.html.twig');
                    $this->sendEmail($this->params['notification']['global_email'], 'Intaro Pinboard can\'t run data aggregation', $body);
                } catch (Exception $e) {
                    $output->writeln('<error>Failed to send lock notification: ' . $e->getMessage() . '</error>');
                }
            }

            return Command::FAILURE;
        }

        if (!touch($lockFile)) {
            $output->writeln('<error>Warning: cannot create ' . $lockFile . '</error>');
        }

        $now = new \DateTime();
        $now = $now->format('Y-m-d H:i:s');

        $delta = new \DateInterval(!empty($this->params['records_lifetime']) ? $this->params['records_lifetime'] : 'P1M');
        $date = new \DateTime();
        $date->sub($delta);

        $params = [
            'created_at' => $date->format('Y-m-d H:i:s'),
        ];

        $tablesForClear = [
            'ipm_report_2_by_hostname_and_server',
            'ipm_report_by_hostname',
            'ipm_report_by_hostname_and_server',
            'ipm_report_by_server_name',
            'ipm_req_time_details',
            'ipm_mem_peak_usage_details',
            'ipm_status_details',
            'ipm_cpu_usage_details',
            'ipm_timer',
        ];

        $sql = '';

        foreach ($tablesForClear as $value) {
            $sql .= '
            DELETE
            FROM
                ' . $value . '
            WHERE
                created_at < :created_at
            ;';
        }
        $db->executeStatement($sql, $params);

        if (!empty($this->params['notification']['enable'])) {
            $sql = '
                SELECT
                    server_name, script_name, status, max(hostname) AS hostname, count(*) AS count
                FROM
                    request
                WHERE
                    status >= 500
                GROUP BY
                    server_name, script_name, status
            ';

            $errorPages = $db->executeQuery($sql)->fetchAllAssociative();

            if (count($errorPages) > 0) {
                try {
                    $this->sendErrorEmails($errorPages);
                } catch (Exception $e) {
                    $output->writeln("<error>Notification sending error\n" . $e->getMessage() . '</error>');
                }
            }

            unset($errorPages);
        }

        $db->beginTransaction();

        $sql = '
            SELECT
                server_name, hostname, COUNT(*) AS cnt
            FROM
                request
            GROUP BY
                server_name, hostname
        ';

        $servers = $db->executeQuery($sql)->fetchAllAssociative();

        $subselectTemplate = '
            (
                SELECT
                    r.%s
                FROM
                    request r
                WHERE
                    r.server_name = r2.server_name AND r.hostname = r2.hostname
                ORDER BY
                    r.%s DESC LIMIT %d, 1
            )
            as %s
        ';

        $sql = '';
        foreach ($servers as $server) {
            $serverName = addslashes((string)$server['server_name']);
            $hostName = addslashes((string)$server['hostname']);
            $sql .= '
                INSERT INTO ipm_report_2_by_hostname_and_server
                    (server_name, hostname, req_time_90, req_time_95, req_time_99, req_time_100,
                     mem_peak_usage_90, mem_peak_usage_95, mem_peak_usage_99, mem_peak_usage_100,
                     cpu_peak_usage_90, cpu_peak_usage_95, cpu_peak_usage_99, cpu_peak_usage_100,
                     doc_size_90, doc_size_95, doc_size_99, doc_size_100, created_at)
                SELECT
                    "' . $serverName . '" AS server_name,
                    "' . $hostName . '" AS hostname,
                    ' . sprintf($subselectTemplate, 'req_time', 'req_time', $server['cnt'] * (1 - 0.90), 'req_time_90') . ',
                    ' . sprintf($subselectTemplate, 'req_time', 'req_time', $server['cnt'] * (1 - 0.95), 'req_time_95') . ',
                    ' . sprintf($subselectTemplate, 'req_time', 'req_time', $server['cnt'] * (1 - 0.99), 'req_time_99') . ',
                    max(req_time) as req_time_100,
                    ' . sprintf($subselectTemplate, 'mem_peak_usage', 'mem_peak_usage', $server['cnt'] * (1 - 0.90), 'mem_peak_usage_90') . ',
                    ' . sprintf($subselectTemplate, 'mem_peak_usage', 'mem_peak_usage', $server['cnt'] * (1 - 0.95), 'mem_peak_usage_95') . ',
                    ' . sprintf($subselectTemplate, 'mem_peak_usage', 'mem_peak_usage', $server['cnt'] * (1 - 0.99), 'mem_peak_usage_99') . ',
                    max(mem_peak_usage) as mem_peak_usage_100,
                    ' . sprintf($subselectTemplate, 'ru_utime', 'ru_utime', $server['cnt'] * (1 - 0.90), 'cpu_peak_usage_90') . ',
                    ' . sprintf($subselectTemplate, 'ru_utime', 'ru_utime', $server['cnt'] * (1 - 0.95), 'cpu_peak_usage_95') . ',
                    ' . sprintf($subselectTemplate, 'ru_utime', 'ru_utime', $server['cnt'] * (1 - 0.99), 'cpu_peak_usage_99') . ',
                    max(ru_utime) as cpu_peak_usage_100,
                    ' . sprintf($subselectTemplate, 'doc_size', 'doc_size', $server['cnt'] * (1 - 0.90), 'doc_size_90') . ',
                    ' . sprintf($subselectTemplate, 'doc_size', 'doc_size', $server['cnt'] * (1 - 0.95), 'doc_size_95') . ',
                    ' . sprintf($subselectTemplate, 'doc_size', 'doc_size', $server['cnt'] * (1 - 0.99), 'doc_size_99') . ',
                    max(doc_size) as doc_size_100,
                    \'' . $now . '\'
                FROM
                    request r2
                WHERE
                    r2.server_name = "' . $serverName . '" and r2.hostname = "' . $hostName . '"
            ;';
        }
        if ($sql !== '') {
            $db->executeStatement($sql);
        }

        $db->commit();

        $sql = '
            INSERT INTO ipm_report_by_hostname
                (
                    req_count, req_per_sec, req_time_total, req_time_percent, req_time_per_sec,
                    ru_utime_total, ru_utime_percent, ru_utime_per_sec,
                    ru_stime_total, ru_stime_percent, ru_stime_per_sec,
                    traffic_total, traffic_percent, traffic_per_sec,
                    hostname, req_time_median, p90, p95, p99, created_at
                )
            SELECT req_count, req_per_sec, req_time_total, req_time_percent, req_time_per_sec,
                    ru_utime_total, ru_utime_percent, ru_utime_per_sec,
                    ru_stime_total, ru_stime_percent, ru_stime_per_sec,
                    traffic_total, traffic_percent, traffic_per_sec,
                    hostname, req_time_median, p90, p95, p99, \'' . $now . '\' FROM ipm_pinba_report_by_hostname_90_95_99;

            INSERT INTO ipm_report_by_hostname_and_server
                (
                    req_count, req_per_sec, req_time_total, req_time_percent, req_time_per_sec,
                    ru_utime_total, ru_utime_percent, ru_utime_per_sec,
                    ru_stime_total, ru_stime_percent, ru_stime_per_sec,
                    traffic_total, traffic_percent, traffic_per_sec,
                    hostname, server_name, req_time_median, p90, p95, p99, created_at
                )
            SELECT req_count, req_per_sec, req_time_total, req_time_percent, req_time_per_sec,
                    ru_utime_total, ru_utime_percent, ru_utime_per_sec,
                    ru_stime_total, ru_stime_percent, ru_stime_per_sec,
                    traffic_total, traffic_percent, traffic_per_sec,
                    hostname, server_name, req_time_median, p90, p95, p99, \'' . $now . '\' FROM ipm_pinba_report_by_hostname_and_server_90_95_99;

            INSERT INTO ipm_report_by_server_name
                (
                    req_count, req_per_sec, req_time_total, req_time_percent, req_time_per_sec,
                    ru_utime_total, ru_utime_percent, ru_utime_per_sec,
                    ru_stime_total, ru_stime_percent, ru_stime_per_sec,
                    traffic_total, traffic_percent, traffic_per_sec,
                    server_name, req_time_median, p90, p95, p99, created_at
                )
            SELECT req_count, req_per_sec, req_time_total, req_time_percent, req_time_per_sec,
                    ru_utime_total, ru_utime_percent, ru_utime_per_sec,
                    ru_stime_total, ru_stime_percent, ru_stime_per_sec,
                    traffic_total, traffic_percent, traffic_per_sec,
                    server_name, req_time_median, p90, p95, p99, \'' . $now . '\' FROM ipm_pinba_report_by_server_90_95_99;
        ';
        $db->executeStatement($sql);

        //insert timers reports
        $sql = '
            INSERT INTO ipm_tag_info
                (
                    `group`, server_name, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, created_at
                )
            SELECT
                    tag1_value, tag2_value, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, \'' . $now . '\'
            FROM
                ipm_pinba_tag_info_group_server_name;

            INSERT INTO ipm_tag_info
                (
                    `group`, server, server_name, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, created_at
                )
            SELECT
                    tag1_value, tag2_value, tag3_value, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, \'' . $now . '\'
            FROM
                ipm_pinba_tag_info_group_server_server_name;

            INSERT INTO ipm_tag_info
                (
                    `group`, server_name, hostname, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, created_at
                )
            SELECT
                    tag1_value, tag2_value, tag3_value, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, \'' . $now . '\'
            FROM
                ipm_pinba_tag_info_group_server_name_hostname;

            INSERT INTO ipm_tag_info
                (
                    `group`, server, server_name, hostname, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, created_at
                )
            SELECT
                    tag1_value, tag2_value, tag3_value, tag4_value, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, \'' . $now . '\'
            FROM
                ipm_pinba_tag_info_group_server_server_name_hostname;

            INSERT INTO ipm_tag_info
                (
                    category, server_name, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, created_at
                )
            SELECT
                    tag1_value, tag2_value, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, \'' . $now . '\'
            FROM
                ipm_pinba_tag_info_category_server_name;

            INSERT INTO ipm_tag_info
                (
                    category, server, server_name, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, created_at
                )
            SELECT
                    tag1_value, tag2_value, tag3_value, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, \'' . $now . '\'
            FROM
                ipm_pinba_tag_info_category_server_server_name;

            INSERT INTO ipm_tag_info
                (
                    category, server_name, hostname, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, created_at
                )
            SELECT
                    tag1_value, tag2_value, tag3_value, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, \'' . $now . '\'
            FROM
                ipm_pinba_tag_info_category_server_name_hostname;

            INSERT INTO ipm_tag_info
                (
                    category, server, server_name, hostname, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, created_at
                )
            SELECT
                    tag1_value, tag2_value, tag3_value, tag4_value, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, \'' . $now . '\'
            FROM
                ipm_pinba_tag_info_category_server_server_name_hostname;
        ';
        $db->executeStatement($sql);

        $sql = '
            INSERT INTO
                ipm_status_details (server_name, hostname, script_name, status, tags, tags_cnt, created_at)
            SELECT
                server_name, hostname, script_name, status, max(tags), max(tags_cnt), FROM_UNIXTIME(max(timestamp))
            FROM
                request
            WHERE
                status >= 500
            GROUP BY
                server_name, hostname, script_name, status
            LIMIT
                25
        ';
        $db->executeStatement($sql);

        $maxReqId = $db->fetchOne('SELECT max(id) FROM ipm_req_time_details');

        $sql = '';
        foreach ($servers as $server) {
            $serverName = addslashes((string)$server['server_name']);
            $hostName = addslashes((string)$server['hostname']);
            $maxReqTime = static::DEFAULT_SLOW_REQ_TIME;
            if (!empty($this->params['logging']['long_request_time']['global'])) {
                $maxReqTime = $this->params['logging']['long_request_time']['global'];
            }
            if (!empty($this->params['logging']['long_request_time'][$server['server_name']])) {
                $maxReqTime = $this->params['logging']['long_request_time'][$server['server_name']];
            }
            $sql .= '
                INSERT INTO ipm_req_time_details
                    (request_id, server_name, hostname, script_name, req_time, mem_peak_usage, tags, tags_cnt, timers_cnt, created_at)
                SELECT
                    max(id), server_name, hostname, script_name, max(req_time), max(mem_peak_usage), max(tags), max(tags_cnt), max(timers_cnt), FROM_UNIXTIME(max(timestamp))
                FROM
                    request
                WHERE
                    server_name = "' . $serverName . '" AND hostname = "' . $hostName . '" AND req_time > ' . (float)$maxReqTime . '
                GROUP BY
                    server_name, hostname, script_name
                ORDER BY
                    max(req_time) DESC
                LIMIT
                    10
            ;';
        }
        if ($sql !== '') {
            $db->executeStatement($sql);

            $sql = '
                SELECT
                    request_id
                FROM
                    ipm_req_time_details
                WHERE
                    id > :max_id
            ';

            $data = $db->executeQuery($sql, ['max_id' => $maxReqId])->fetchAllAssociative();

            $ids = [];
            foreach ($data as $item) {
                $ids[] = $item['request_id'];
            }
            unset($data);

            if (count($ids)) {
                $sql = '
                    INSERT INTO ipm_timer
                        (timer_id, request_id, hit_count, value, tag_name, tag_value, created_at)
                    SELECT
                        t.id, t.request_id, t.hit_count, t.value, tag.name as tag_name, tt.value as tag_value, FROM_UNIXTIME(r.timestamp)
                    FROM
                        timer t
                    JOIN
                        request r ON t.request_id = r.id
                    JOIN
                        timertag tt ON tt.timer_id = t.id
                    JOIN
                        tag ON tt.tag_id = tag.id
                    WHERE
                        t.request_id IN (' . implode(', ', $ids) . ')
                ';

                $db->executeStatement($sql);
            }
        }

        $sql = '';
        foreach ($servers as $server) {
            $serverName = addslashes((string)$server['server_name']);
            $hostName = addslashes((string)$server['hostname']);
            $maxMemoryUsage = static::DEFAULT_HEAVY_PAGE_MEMORY;
            if (!empty($this->params['logging']['heavy_request']['global'])) {
                $maxMemoryUsage = $this->params['logging']['heavy_request']['global'];
            }
            if (!empty($this->params['logging']['heavy_request'][$server['server_name']])) {
                $maxMemoryUsage = $this->params['logging']['heavy_request'][$server['server_name']];
            }

            $sql .= '
                INSERT INTO ipm_mem_peak_usage_details
                    (server_name, hostname, script_name, mem_peak_usage, tags, tags_cnt, created_at)
                SELECT
                    server_name, hostname, script_name, max(mem_peak_usage), max(tags), max(tags_cnt), FROM_UNIXTIME(max(timestamp))
                FROM
                    request
                WHERE
                    server_name = "' . $serverName . '" AND hostname = "' . $hostName . '" AND mem_peak_usage > ' . (int)$maxMemoryUsage . '
                GROUP BY
                    server_name, hostname, script_name
                ORDER BY
                    max(mem_peak_usage) DESC
                LIMIT
                    10
            ;';
        }
        if ($sql !== '') {
            $db->executeStatement($sql);
        }

        $sql = '';
        foreach ($servers as $server) {
            $serverName = addslashes((string)$server['server_name']);
            $hostName = addslashes((string)$server['hostname']);
            $maxCPUUsage = static::DEFAULT_HEAVY_PAGE_CPU;
            if (!empty($this->params['logging']['heavy_cpu_request']['global'])) {
                $maxCPUUsage = $this->params['logging']['heavy_cpu_request']['global'];
            }
            if (!empty($this->params['logging']['heavy_cpu_request'][$server['server_name']])) {
                $maxCPUUsage = $this->params['logging']['heavy_cpu_request'][$server['server_name']];
            }

            $sql .= '
                  INSERT INTO ipm_cpu_usage_details
                      (server_name, hostname, script_name, cpu_peak_usage, tags, tags_cnt, created_at)
                  SELECT
                      server_name, hostname, script_name, max(ru_utime), max(tags), max(tags_cnt), FROM_UNIXTIME(max(timestamp))
                  FROM
                      request
                  WHERE
                      server_name = "' . $serverName . '" AND hostname = "' . $hostName . '" AND ru_utime > ' . (int)$maxCPUUsage . '
                  GROUP BY
                      server_name, hostname, script_name
                  ORDER BY
                      max(ru_utime) DESC
                  LIMIT
                      10
            ;';
        }
        if ($sql !== '') {
            $db->executeStatement($sql);
        }

        // notification about abrupt drawdown of indicators
        $values = $this->getBorderOutValues($db, $servers);
        $this->sendBorderOutEmails($values);

        $output->writeln('<info>Data are aggregated successfully</info>');

        if (file_exists($lockFile) && !unlink($lockFile)) {
            $output->writeln('<error>Error: cannot remove ' . $lockFile . ' file, you must remove it manually and check server settings.</error>');
        }

        return Command::SUCCESS;
    }


    /**
     * @param list<array<string, mixed>> $servers
     * @return array<string, mixed>
     */
    protected function getBorderOutValues(Connection $db, array $servers): array
    {
        $d = new \DateTime();
        $di = new \DateInterval(
            !empty($this->params['aggregation_period']) ? $this->params['aggregation_period'] : 'P15M'
        );
        //2 aggregations ago
        $d->sub($di);
        $d->sub($di);

        $result = [];
        foreach ($servers as $server) {
            if (empty($result[$server['server_name']])) {
                $result[$server['server_name']] = [
                    'req_per_sec' => $server['cnt'] / ($di->format('%i') ?: 15) / 60,
                ];
            }
        }

        //req_time
        foreach (['95', '90'] as $perc) {
            $sql = '
                SELECT
                  server_name,
                  hostname,
                  req_time_' . $perc . ',
                  created_at
                FROM
                  ipm_report_2_by_hostname_and_server
                WHERE
                  server_name IS NOT NULL AND server_name != "unknown" AND hostname IS NOT NULL AND created_at >= :created_at
                ORDER BY
                  created_at DESC
            ';

            $data = $db->executeQuery($sql, [
                'created_at' => $d->format('Y-m-d H:i:s')
            ])->fetchAllAssociative();

            $finalData = [];
            foreach ($data as $row) {
                if (!empty($result[$row['server_name']])) {
                    $finalData[$row['server_name']][$row['hostname']][] = [
                        'value' => $row['req_time_' . $perc],
                        'created_at' => $row['created_at'],
                    ];
                }
            }

            unset($data);

            $defaultBorder = !empty($this->params['notification']['border']['req_time']['global'])
                ? (float)$this->params['notification']['border']['req_time']['global']
                : static::DEFAULT_REQ_TIME_BORDER;

            foreach ($finalData as $server => $hosts) {
                $border = !empty($this->params['notification']['border']['req_time'][$server])
                    ? (float)$this->params['notification']['border']['req_time'][$server]
                    : $defaultBorder;

                foreach ($hosts as $host => $values) {
                    if (count($values) > 1) {
                        if (
                            $result[$server]['req_per_sec'] >= 0.2 &&
                            (
                                $values[0]['value'] >= $border && $values[1]['value'] < $border ||
                                $values[0]['value'] < $border && $values[1]['value'] >= $border
                            )
                        ) {
                            $result[$server]['req_time_' . $perc][] = [
                                'status' => $values[0]['value'] < $values[1]['value'] ? 'OK' : 'PROBLEM',
                                'hostname' => $host,
                                'current' => $values[0]['value'],
                                'prev' => $values[1]['value'],
                                'current_formatted' => number_format($values[0]['value'] * 1000, 0, '.', '') . ' ms',
                                'prev_formatted' => number_format($values[1]['value'] * 1000, 0, '.', '') . ' ms',
                                'current_date' => $values[0]['created_at'],
                                'prev_date' => $values[1]['created_at'],
                                'border' => number_format($border * 1000, 0, '.', '') . ' ms',
                            ];
                        }
                    }
                }
            }

            unset($finalData);
        }

        foreach ($result as $server => $values) {
            if (count($values) < 2) {
                unset($result[$server]);
            } else {
                unset($result[$server]['req_per_sec']);
            }
        }

        return $result;
    }

    /**
     * @param array<string, mixed> $data
     * @param string|list<string> $address
     */
    private function sendBorderOutEmail(array $data, string $subject, string|array $address): void
    {
        if (count($data) > 0) {
            $body = $this->twig->render('drawdown_notification.html.twig', ['data' => $data]);
            $this->sendEmail($address, $subject, $body);
        }
    }

    /** @param array<string, mixed> $data */
    private function sendBorderOutEmails(array $data): void
    {
        $subject = 'Intaro Pinboard has detected a drawdown of indicators';

        if (!empty($this->params['notification']['global_email'])) {
            $status = [];
            $d = [];

            foreach ($data as $server => $values) {
                if ($this->isNotIgnore($server)) {
                    $d[$server] = $values;
                    foreach ($values as $indicator) {
                        foreach ($indicator as $host) {
                            $status[] = $host['status'];
                        }
                    }
                }
            }

            $status = array_unique($status);
            $mailSubject = '[' . implode(', ', $status) . "] $subject";

            $this->sendBorderOutEmail($d, $mailSubject, $this->params['notification']['global_email']);

            unset($d);
        }

        if (!empty($this->params['notification']['list'])) {
            foreach ($this->params['notification']['list'] as $item) {
                $status = [];
                $d = [];

                foreach ($data as $server => $values) {
                    if ($this->isNotIgnore($server) && preg_match("/{$item['hosts']}/", $server)) {
                        $d[$server] = $values;

                        foreach ($values as $indicator) {
                            foreach ($indicator as $host) {
                                $status[] = $host['status'];
                            }
                        }
                    }
                }

                $status = array_unique($status);
                $mailSubject = '[' . implode(', ', $status) . '] ' . $subject;

                $this->sendBorderOutEmail($d, $mailSubject, $item['email']);

                unset($d);
            }
        }
    }
}
