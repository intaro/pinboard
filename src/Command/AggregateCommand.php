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
    private const float MYSQL_DOUBLE_MAX = 1.7976931348623157e308;
    private const float PINBA_FLOAT_SENTINEL_MAX = 3.4e38;
    private const string MYSQL_NUMERIC_PATTERN = '^-?([0-9]+(\\.[0-9]+)?|\\.[0-9]+)([eE][+-]?[0-9]+)?$';

    private AggregateConfig $config;
    private string $projectDir;

    public const float DEFAULT_REQ_TIME_BORDER = 1.5;
    public const float DEFAULT_SLOW_REQ_TIME = 1.5;
    public const int DEFAULT_HEAVY_PAGE_MEMORY = 30000;
    public const int DEFAULT_HEAVY_PAGE_CPU = 1;
    public const int DEFAULT_MIN_ERROR_CODE = 500;

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

    private function buildConfig(): AggregateConfig
    {
        return new AggregateConfig(
            recordsLifetime: $this->envString('APP_RECORDS_LIFETIME', 'P1M'),
            aggregationPeriod: $this->envString('APP_AGGREGATION_PERIOD', 'PT15M'),
            longRequestTime: $this->buildFloatMap(
                'APP_LOGGING_LONG_REQUEST_TIME_GLOBAL',
                (string) static::DEFAULT_SLOW_REQ_TIME,
                'APP_LOGGING_LONG_REQUEST_TIME_MAP'
            ),
            heavyRequest: $this->buildFloatMap(
                'APP_LOGGING_HEAVY_REQUEST_GLOBAL',
                (string) static::DEFAULT_HEAVY_PAGE_MEMORY,
                'APP_LOGGING_HEAVY_REQUEST_MAP'
            ),
            heavyCpuRequest: $this->buildFloatMap(
                'APP_LOGGING_HEAVY_CPU_REQUEST_GLOBAL',
                (string) static::DEFAULT_HEAVY_PAGE_CPU,
                'APP_LOGGING_HEAVY_CPU_REQUEST_MAP'
            ),
            notificationEnable: $this->envBool('APP_NOTIFICATION_ENABLE', false),
            notificationSender: $this->envString('APP_NOTIFICATION_SENDER', 'noreply@pinboard'),
            notificationGlobalEmail: $this->envString('APP_NOTIFICATION_GLOBAL_EMAIL', ''),
            notificationIgnore: $this->envCsv('APP_NOTIFICATION_IGNORE'),
            notificationList: $this->buildNotificationList('APP_NOTIFICATION_LIST_JSON'),
            reqTimeBorder: $this->buildFloatMap(
                'APP_NOTIFICATION_REQ_TIME_BORDER_GLOBAL',
                (string) static::DEFAULT_REQ_TIME_BORDER,
                'APP_NOTIFICATION_REQ_TIME_BORDER_MAP'
            ),
            minErrorCode: $this->envInt('APP_MIN_ERROR_CODE', static::DEFAULT_MIN_ERROR_CODE),
        );
    }

    /** @return array<string, float> */
    private function buildFloatMap(string $globalEnv, string $globalDefault, string $mapEnv): array
    {
        $result = ['global' => (float) $this->envString($globalEnv, $globalDefault)];
        $raw = $this->envJson($mapEnv, []);
        if (is_array($raw)) {
            foreach ($raw as $k => $v) {
                if (is_string($k) && is_numeric($v)) {
                    $result[$k] = (float) $v;
                }
            }
        }
        return $result;
    }

    /** @return list<array{hosts: string, email: string}> */
    private function buildNotificationList(string $envVar): array
    {
        $raw = $this->envJson($envVar, []);
        if (!is_array($raw)) {
            return [];
        }
        $result = [];
        foreach ($raw as $item) {
            if (!is_array($item)) {
                continue;
            }
            $hosts = $item['hosts'] ?? null;
            $email = $item['email'] ?? null;
            if (!is_string($hosts) || !is_string($email)) {
                continue;
            }
            $result[] = ['hosts' => $hosts, 'email' => $email];
        }
        return $result;
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
        return $this->config->notificationSender;
    }

    private function safePinbaFloat(string $expression): string
    {
        // Normalize through CHAR first to avoid MySQL warning on malformed
        // numeric payloads coming from Pinba virtual tables before CASE can
        // short-circuit. Scientific notation is accepted for large values.
        // Pinba engine percentiles are exposed as FLOAT columns; FLT_MAX-like
        // values (for example 3.40282e38, which is the rounded string form of
        // the 32-bit float sentinel) are treated as broken sentinels and are
        // nulled instead of being persisted into report tables.
        return sprintf(
            "CASE
                WHEN TRIM(CONVERT(%1\$s, CHAR)) REGEXP '%2\$s'
                    AND ABS(CAST(TRIM(CONVERT(%1\$s, CHAR)) AS DOUBLE)) < %4\$s
                    THEN LEAST(
                        GREATEST(CAST(TRIM(CONVERT(%1\$s, CHAR)) AS DOUBLE), -%3\$s),
                        %3\$s
                    )
                ELSE NULL
            END",
            $expression,
            self::MYSQL_NUMERIC_PATTERN,
            self::MYSQL_DOUBLE_MAX,
            self::PINBA_FLOAT_SENTINEL_MAX
        );
    }

    private function safePinbaPercentiles(): string
    {
        return sprintf(
            '%s AS p90, %s AS p95, %s AS p99',
            $this->safePinbaFloat('p90'),
            $this->safePinbaFloat('p95'),
            $this->safePinbaFloat('p99')
        );
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
        foreach ($this->config->notificationIgnore as $hostToIgnore) {
            if (preg_match('#' . $hostToIgnore . '#', $host)) {
                return false;
            }
        }

        return true;
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
        if ($this->config->notificationGlobalEmail !== '') {
            $pages = [];
            foreach ($errorPages as $page) {
                $serverName = is_string($page['server_name']) ? $page['server_name'] : '';
                if ($serverName !== '' && $this->isNotIgnore($serverName)) {
                    $pages[$serverName][] = $page;
                }
            }
            $this->sendErrorPages($pages, $this->config->notificationGlobalEmail);
        }

        foreach ($this->config->notificationList as $item) {
            $pages = [];
            foreach ($errorPages as $page) {
                $serverName = is_string($page['server_name']) ? $page['server_name'] : '';
                if ($serverName !== '' && preg_match('/' . $item['hosts'] . '/', $serverName) && $this->isNotIgnore($serverName)) {
                    $pages[$serverName][] = $page;
                }
            }
            $this->sendErrorPages($pages, $item['email']);
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->config = $this->buildConfig();

        $db = $this->db;

        try {
            $db->executeQuery('SELECT 1');
        } catch (\Throwable $e) {
            $output->writeln('<error>Can\'t connect to MySQL server</error>');

            return Command::FAILURE;
        }

        $lockFile = $this->projectDir . '/var/aggregate.lock';
        $lockHandle = @fopen($lockFile, 'c+');
        if ($lockHandle === false) {
            $output->writeln('<error>Cannot open lock file ' . $lockFile . '</error>');

            return Command::FAILURE;
        }

        if (!flock($lockHandle, LOCK_EX | LOCK_NB)) {
            $output->writeln('<error>Cannot run data aggregation: another instance is already executing.</error>');

            if ($this->config->notificationGlobalEmail !== '') {
                try {
                    $body = $this->twig->render('lock_notification.html.twig');
                    $this->sendEmail($this->config->notificationGlobalEmail, 'Intaro Pinboard can\'t run data aggregation', $body);
                } catch (\Throwable $e) {
                    $output->writeln('<error>Failed to send lock notification: ' . $e->getMessage() . '</error>');
                }
            }

            fclose($lockHandle);

            return Command::FAILURE;
        }

        $lockMetadata = sprintf(
            "pid=%d\nstarted_at=%s\n",
            getmypid() ?: 0,
            date(DATE_ATOM)
        );
        if (
            ftruncate($lockHandle, 0) === false ||
            rewind($lockHandle) === false ||
            fwrite($lockHandle, $lockMetadata) === false ||
            fflush($lockHandle) === false
        ) {
            $output->writeln('<comment>Warning: failed to write lock metadata to ' . $lockFile . '</comment>');
        }

        try {
            $now = new \DateTime();
            $now = $now->format('Y-m-d H:i:s');

            $delta = new \DateInterval($this->config->recordsLifetime !== '' ? $this->config->recordsLifetime : 'P1M');
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
                'ipm_tag_info',
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

            if ($this->config->notificationEnable) {
                $sql = '
                    SELECT
                        server_name, script_name, status, max(hostname) AS hostname, count(*) AS count
                    FROM
                        request
                    WHERE
                        status >= ' . $this->config->minErrorCode . '
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
                $serverName = is_string($server['server_name']) ? $server['server_name'] : '';
                $hostName = is_string($server['hostname']) ? $server['hostname'] : '';
                $cnt = is_numeric($server['cnt']) ? (int) $server['cnt'] : 0;
                if ($serverName === '' || $hostName === '' || $cnt === 0) {
                    continue;
                }
                $serverNameEsc = addslashes($serverName);
                $hostNameEsc = addslashes($hostName);
                $sql .= '
                INSERT INTO ipm_report_2_by_hostname_and_server
                    (server_name, hostname, req_time_90, req_time_95, req_time_99, req_time_100,
                     mem_peak_usage_90, mem_peak_usage_95, mem_peak_usage_99, mem_peak_usage_100,
                     cpu_peak_usage_90, cpu_peak_usage_95, cpu_peak_usage_99, cpu_peak_usage_100,
                     doc_size_90, doc_size_95, doc_size_99, doc_size_100, created_at)
                SELECT
                    "' . $serverNameEsc . '" AS server_name,
                    "' . $hostNameEsc . '" AS hostname,
                    ' . sprintf($subselectTemplate, 'req_time', 'req_time', $cnt * (1 - 0.90), 'req_time_90') . ',
                    ' . sprintf($subselectTemplate, 'req_time', 'req_time', $cnt * (1 - 0.95), 'req_time_95') . ',
                    ' . sprintf($subselectTemplate, 'req_time', 'req_time', $cnt * (1 - 0.99), 'req_time_99') . ',
                    max(req_time) as req_time_100,
                    ' . sprintf($subselectTemplate, 'mem_peak_usage', 'mem_peak_usage', $cnt * (1 - 0.90), 'mem_peak_usage_90') . ',
                    ' . sprintf($subselectTemplate, 'mem_peak_usage', 'mem_peak_usage', $cnt * (1 - 0.95), 'mem_peak_usage_95') . ',
                    ' . sprintf($subselectTemplate, 'mem_peak_usage', 'mem_peak_usage', $cnt * (1 - 0.99), 'mem_peak_usage_99') . ',
                    max(mem_peak_usage) as mem_peak_usage_100,
                    ' . sprintf($subselectTemplate, 'ru_utime', 'ru_utime', $cnt * (1 - 0.90), 'cpu_peak_usage_90') . ',
                    ' . sprintf($subselectTemplate, 'ru_utime', 'ru_utime', $cnt * (1 - 0.95), 'cpu_peak_usage_95') . ',
                    ' . sprintf($subselectTemplate, 'ru_utime', 'ru_utime', $cnt * (1 - 0.99), 'cpu_peak_usage_99') . ',
                    max(ru_utime) as cpu_peak_usage_100,
                    ' . sprintf($subselectTemplate, 'doc_size', 'doc_size', $cnt * (1 - 0.90), 'doc_size_90') . ',
                    ' . sprintf($subselectTemplate, 'doc_size', 'doc_size', $cnt * (1 - 0.95), 'doc_size_95') . ',
                    ' . sprintf($subselectTemplate, 'doc_size', 'doc_size', $cnt * (1 - 0.99), 'doc_size_99') . ',
                    max(doc_size) as doc_size_100,
                    \'' . $now . '\'
                FROM
                    request r2
                WHERE
                    r2.server_name = "' . $serverNameEsc . '" and r2.hostname = "' . $hostNameEsc . '"
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
                    hostname, req_time_median, ' . $this->safePinbaPercentiles() . ', \'' . $now . '\' FROM ipm_pinba_report_by_hostname_90_95_99;

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
                    hostname, server_name, req_time_median, ' . $this->safePinbaPercentiles() . ', \'' . $now . '\' FROM ipm_pinba_report_by_hostname_and_server_90_95_99;

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
                    server_name, req_time_median, ' . $this->safePinbaPercentiles() . ', \'' . $now . '\' FROM ipm_pinba_report_by_server_90_95_99;
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
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, ' . $this->safePinbaPercentiles() . ', \'' . $now . '\'
            FROM
                ipm_pinba_tag_info_group_server_name;

            INSERT INTO ipm_tag_info
                (
                    `group`, server, server_name, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, created_at
                )
            SELECT
                    tag1_value, tag2_value, tag3_value, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, ' . $this->safePinbaPercentiles() . ', \'' . $now . '\'
            FROM
                ipm_pinba_tag_info_group_server_server_name;

            INSERT INTO ipm_tag_info
                (
                    `group`, server_name, hostname, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, created_at
                )
            SELECT
                    tag1_value, tag2_value, tag3_value, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, ' . $this->safePinbaPercentiles() . ', \'' . $now . '\'
            FROM
                ipm_pinba_tag_info_group_server_name_hostname;

            INSERT INTO ipm_tag_info
                (
                    `group`, server, server_name, hostname, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, created_at
                )
            SELECT
                    tag1_value, tag2_value, tag3_value, tag4_value, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, ' . $this->safePinbaPercentiles() . ', \'' . $now . '\'
            FROM
                ipm_pinba_tag_info_group_server_server_name_hostname;

            INSERT INTO ipm_tag_info
                (
                    category, server_name, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, created_at
                )
            SELECT
                    tag1_value, tag2_value, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, ' . $this->safePinbaPercentiles() . ', \'' . $now . '\'
            FROM
                ipm_pinba_tag_info_category_server_name;

            INSERT INTO ipm_tag_info
                (
                    category, server, server_name, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, created_at
                )
            SELECT
                    tag1_value, tag2_value, tag3_value, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, ' . $this->safePinbaPercentiles() . ', \'' . $now . '\'
            FROM
                ipm_pinba_tag_info_category_server_server_name;

            INSERT INTO ipm_tag_info
                (
                    category, server_name, hostname, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, created_at
                )
            SELECT
                    tag1_value, tag2_value, tag3_value, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, ' . $this->safePinbaPercentiles() . ', \'' . $now . '\'
            FROM
                ipm_pinba_tag_info_category_server_name_hostname;

            INSERT INTO ipm_tag_info
                (
                    category, server, server_name, hostname, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, p90, p95, p99, created_at
                )
            SELECT
                    tag1_value, tag2_value, tag3_value, tag4_value, req_count, req_per_sec, hit_count,
                    hit_per_sec, timer_value, timer_median, ru_utime_value, ru_stime_value, ' . $this->safePinbaPercentiles() . ', \'' . $now . '\'
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
                status >= ' . $this->config->minErrorCode . '
            GROUP BY
                server_name, hostname, script_name, status
            LIMIT
                25
        ';
            $db->executeStatement($sql);

            $maxReqId = $db->fetchOne('SELECT max(id) FROM ipm_req_time_details');

            $sql = '';
            foreach ($servers as $server) {
                $serverName = is_string($server['server_name']) ? $server['server_name'] : '';
                $hostName = is_string($server['hostname']) ? $server['hostname'] : '';
                if ($serverName === '' || $hostName === '') {
                    continue;
                }
                $serverNameEsc = addslashes($serverName);
                $hostNameEsc = addslashes($hostName);
                $maxReqTime = $this->config->longRequestTime['global'] ?? static::DEFAULT_SLOW_REQ_TIME;
                if (isset($this->config->longRequestTime[$serverName])) {
                    $maxReqTime = $this->config->longRequestTime[$serverName];
                }
                $sql .= '
                INSERT INTO ipm_req_time_details
                    (request_id, server_name, hostname, script_name, req_time, mem_peak_usage, tags, tags_cnt, timers_cnt, created_at)
                SELECT
                    request_id, server_name, hostname, script_name, req_time, mem_peak_usage, tags, tags_cnt, timers_cnt, created_at
                FROM
                    (
                        SELECT
                            r.id AS request_id,
                            r.server_name,
                            r.hostname,
                            r.script_name,
                            r.req_time,
                            r.mem_peak_usage,
                            r.tags,
                            r.tags_cnt,
                            r.timers_cnt,
                            FROM_UNIXTIME(r.timestamp) AS created_at,
                            ROW_NUMBER() OVER (
                                PARTITION BY r.server_name, r.hostname, r.script_name
                                ORDER BY r.req_time DESC, r.timestamp DESC, r.id DESC
                            ) AS rn
                        FROM
                            request r
                        WHERE
                            r.server_name = "' . $serverNameEsc . '" AND r.hostname = "' . $hostNameEsc . '" AND r.req_time > ' . $maxReqTime . '
                    ) ranked_request
                WHERE
                    rn = 1
                ORDER BY
                    req_time DESC
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
                    $reqId = $item['request_id'];
                    if (is_int($reqId)) {
                        $ids[] = (string) $reqId;
                    } elseif (is_string($reqId) && $reqId !== '') {
                        $ids[] = $reqId;
                    }
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
                $serverName = is_string($server['server_name']) ? $server['server_name'] : '';
                $hostName = is_string($server['hostname']) ? $server['hostname'] : '';
                if ($serverName === '' || $hostName === '') {
                    continue;
                }
                $serverNameEsc = addslashes($serverName);
                $hostNameEsc = addslashes($hostName);
                $maxMemoryUsage = $this->config->heavyRequest['global'] ?? static::DEFAULT_HEAVY_PAGE_MEMORY;
                if (isset($this->config->heavyRequest[$serverName])) {
                    $maxMemoryUsage = $this->config->heavyRequest[$serverName];
                }

                $sql .= '
                INSERT INTO ipm_mem_peak_usage_details
                    (server_name, hostname, script_name, mem_peak_usage, tags, tags_cnt, created_at)
                SELECT
                    server_name, hostname, script_name, max(mem_peak_usage), max(tags), max(tags_cnt), FROM_UNIXTIME(max(timestamp))
                FROM
                    request
                WHERE
                    server_name = "' . $serverNameEsc . '" AND hostname = "' . $hostNameEsc . '" AND mem_peak_usage > ' . (int) $maxMemoryUsage . '
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
                $serverName = is_string($server['server_name']) ? $server['server_name'] : '';
                $hostName = is_string($server['hostname']) ? $server['hostname'] : '';
                if ($serverName === '' || $hostName === '') {
                    continue;
                }
                $serverNameEsc = addslashes($serverName);
                $hostNameEsc = addslashes($hostName);
                $maxCPUUsage = $this->config->heavyCpuRequest['global'] ?? static::DEFAULT_HEAVY_PAGE_CPU;
                if (isset($this->config->heavyCpuRequest[$serverName])) {
                    $maxCPUUsage = $this->config->heavyCpuRequest[$serverName];
                }

                $sql .= '
                  INSERT INTO ipm_cpu_usage_details
                      (server_name, hostname, script_name, cpu_peak_usage, tags, tags_cnt, created_at)
                  SELECT
                      server_name, hostname, script_name, max(ru_utime), max(tags), max(tags_cnt), FROM_UNIXTIME(max(timestamp))
                  FROM
                      request
                  WHERE
                      server_name = "' . $serverNameEsc . '" AND hostname = "' . $hostNameEsc . '" AND ru_utime > ' . (int) $maxCPUUsage . '
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

            return Command::SUCCESS;
        } finally {
            ftruncate($lockHandle, 0);
            fflush($lockHandle);
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }


    /**
     * @param list<array<string, mixed>> $servers
     * @return array<string, array<string, list<array{
     *   status: string,
     *   hostname: string,
     *   current: float,
     *   prev: float,
     *   current_formatted: string,
     *   prev_formatted: string,
     *   current_date: string,
     *   prev_date: string,
     *   border: string,
     * }>>>
     */
    protected function getBorderOutValues(Connection $db, array $servers): array
    {
        $d = new \DateTime();
        $aggregationPeriod = $this->config->aggregationPeriod !== '' ? $this->config->aggregationPeriod : 'PT15M';
        $di = new \DateInterval($aggregationPeriod);
        //2 aggregations ago
        $d->sub($di);
        $d->sub($di);

        $reqPerSec = [];
        foreach ($servers as $server) {
            $serverName = is_string($server['server_name']) ? $server['server_name'] : '';
            $cnt = is_numeric($server['cnt']) ? (int) $server['cnt'] : 0;
            if ($serverName !== '' && !isset($reqPerSec[$serverName])) {
                $reqPerSec[$serverName] = $cnt / ($di->format('%i') ?: 15) / 60;
            }
        }

        $result = [];

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
                $rowServerName = is_string($row['server_name']) ? $row['server_name'] : '';
                $rowHostName = is_string($row['hostname']) ? $row['hostname'] : '';
                if ($rowServerName === '' || $rowHostName === '' || !isset($reqPerSec[$rowServerName])) {
                    continue;
                }
                $reqTimeRaw = $row['req_time_' . $perc];
                $reqTimeVal = is_numeric($reqTimeRaw) ? (float) $reqTimeRaw : 0.0;
                $createdAt = is_string($row['created_at']) ? $row['created_at'] : '';
                $finalData[$rowServerName][$rowHostName][] = [
                    'value' => $reqTimeVal,
                    'created_at' => $createdAt,
                ];
            }

            unset($data);

            $defaultBorder = $this->config->reqTimeBorder['global'] ?? static::DEFAULT_REQ_TIME_BORDER;

            foreach ($finalData as $server => $hosts) {
                $border = $this->config->reqTimeBorder[$server] ?? $defaultBorder;

                foreach ($hosts as $host => $values) {
                    if (count($values) > 1) {
                        if (
                            ($reqPerSec[$server] ?? 0.0) >= 0.2 &&
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

        return $result;
    }

    /**
     * @param array<string, array<string, list<array{status: string, hostname: string, current: float, prev: float, current_formatted: string, prev_formatted: string, current_date: string, prev_date: string, border: string}>>> $data
     * @param string|list<string> $address
     */
    private function sendBorderOutEmail(array $data, string $subject, string|array $address): void
    {
        if (count($data) > 0) {
            $body = $this->twig->render('drawdown_notification.html.twig', ['data' => $data]);
            $this->sendEmail($address, $subject, $body);
        }
    }

    /** @param array<string, array<string, list<array{status: string, hostname: string, current: float, prev: float, current_formatted: string, prev_formatted: string, current_date: string, prev_date: string, border: string}>>> $data */
    private function sendBorderOutEmails(array $data): void
    {
        $subject = 'Intaro Pinboard has detected a drawdown of indicators';

        if ($this->config->notificationGlobalEmail !== '') {
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

            $this->sendBorderOutEmail($d, $mailSubject, $this->config->notificationGlobalEmail);

            unset($d);
        }

        foreach ($this->config->notificationList as $item) {
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
