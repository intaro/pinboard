<?php
namespace Pinboard\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

class AggregateCommand extends Command
{
    protected $mailer;
    protected $params;
    protected $app;
    protected $output;

    const DEFAULT_REQ_TIME_BORDER = 1.5;
    const DEFAULT_SLOW_REQ_TIME = 1.5;
    const DEFAULT_HEAVY_PAGE_MEMORY = 30000;
    const DEFAULT_HEAVY_PAGE_CPU = 1;

    protected function configure()
    {
        $this
            ->setName('aggregate')
            ->setDescription('Aggregate data from source tables and save to report tables')
        ;
    }

    protected function initMailer()
    {
        if (isset($this->params['smtp'])) {
            $transport = \Swift_SmtpTransport::newInstance()
                ->setHost($this->params['smtp']['server'])
                ->setPort($this->params['smtp']['port'])
            ;
            if (isset($this->params['smtp']['username'])) {
                $transport->setUsername($this->params['smtp']['username']);
            }
            if (isset($this->params['smtp']['password'])) {
                $transport->setPassword($this->params['smtp']['password']);
            }
            if (isset($this->params['smtp']['encryption'])) {
                $transport->setEncryption($this->params['smtp']['encryption']);
            }
            if (isset($this->params['smtp']['auth_mode'])) {
                $transport->setAuthMode($this->params['smtp']['auth_mode']);
            }
        }
        else {
            $transport = \Swift_MailTransport::newInstance();
        }

        $this->mailer = \Swift_Mailer::newInstance($transport);
    }

    protected function sendEmail($message)
    {
        if ($this->mailer) {
            try {
                $this->mailer->send($message);
            }
            catch(\Exception $e) {
                $this->output->writeln('<error>Failed to send email message. Error output:</error>');
                $this->output->writeln('<error></error>');
                $this->output->writeln('<error>' . $e->getMessage() . '</error>');
                $this->output->writeln('<error></error>');
            }
        }
    }

    private function isNotIgnore($host) {
        $notIgnore = true;
        if (isset($this->params['notification']['ignore'])) {
            foreach($this->params['notification']['ignore'] as $hostToIgnore) {
                if(preg_match('#' . $hostToIgnore . '#', $host)) {
                    $notIgnore = false;
                    break;
                }
            }
        }

        return $notIgnore;
    }

    private function sendErrorPages($pages, $message, $address) {
        if (count($pages) > 0) {
            $body = $this->app['twig']->render('error_notification.html.twig', array('pages' => $pages));
            $message->setBody($body);
            $message->setTo($address);

            $this->sendEmail($message);

            unset($body);
        }
    }

    private function sendErrorEmails($errorPages)
    {
        $message = \Swift_Message::newInstance()
            ->setSubject('Intaro Pinboard found error pages')
            ->setContentType('text/html')
            ->setFrom(isset($this->params['notification']['sender']) ? $this->params['notification']['sender'] : 'noreply@pinboard');

        if (isset($this->params['notification']['global_email'])) {
            $pages = array();
            foreach ($errorPages as $page) {
                if($this->isNotIgnore($page['server_name'])) {
                    $pages[$page['server_name']][] = $page;
                }
            }
            $this->sendErrorPages($pages, $message, $this->params['notification']['global_email']);
        }

        if (isset($this->params['notification']['list'])) {
            foreach ($this->params['notification']['list'] as $item) {
                $pages = array();
                foreach ($errorPages as $page) {
                    if (preg_match('/' . $item['hosts'] . '/', $page['server_name']) && $this->isNotIgnore($page['server_name'])) {
                        $pages[$page['server_name']][] = $page;
                    }
                }
                $this->sendErrorPages($pages, $message, $item['email']);
            }
        }

        unset($message);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->app = $this->getApplication()->getSilex();
        $this->app->boot();
        $this->params = $this->app['params'];
        $this->output = $output;

        $db = $this->app['db'];

        try {
            $this->initMailer();
        }
        catch(\Exception $e) {
            $output->writeln('<error>Can\'t init mailer</error>');

            return;
        }

        try {
            $db->connect();
        }
        catch(\PDOException $e) {
            $output->writeln('<error>Can\'t connect to MySQL server</error>');

            return;
        }

        if(file_exists( __FILE__ . '.lock')) {
            $output->writeln('<error>Cannot run data aggregation: the another instance of this script is already executing. Otherwise, remove ' . __FILE__ . '.lock file</error>');

            if ($this->mailer && isset($this->params['notification']['global_email'])) {
                $body = $this->app['twig']->render('lock_notification.html.twig');

                $message = \Swift_Message::newInstance()
                    ->setSubject('Intaro Pinboard can\'t run data aggregation')
                    ->setContentType('text/html')
                    ->setFrom(isset($this->params['notification']['sender']) ? $this->params['notification']['sender'] : 'noreply@pinboard')
                    ->setTo($this->params['notification']['global_email'])
                    ->setBody($body);
                    ;

                $this->sendEmail($message);
            }

            return;
        }

        if(!touch( __FILE__ . '.lock')) {
            $output->writeln('<error>Warning: cannot create ' . __FILE__ . '.lock file</error>');
        }

        $delta = new \DateInterval(isset($this->params['records_lifetime']) ? $this->params['records_lifetime'] : 'P1M');
        $date = new \DateTime();
        $date->sub($delta);

        $params = array(
            'created_at' => $date->format('Y-m-d H:i:s'),
        );

        $tablesForClear = array(
            "ipm_report_2_by_hostname_and_server",
            "ipm_report_by_hostname",
            "ipm_report_by_hostname_and_server",
            "ipm_report_by_server_name",
            "ipm_req_time_details",
            "ipm_mem_peak_usage_details",
            "ipm_status_details",
            "ipm_cpu_usage_details",
        );

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
        if ($sql != '')
            $db->executeQuery($sql, $params);

        if (isset($this->params['notification']['enable']) && $this->params['notification']['enable']) {
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

            $errorPages = $db->fetchAll($sql);

            if (count($errorPages) > 0) {
                try {
                    $this->sendErrorEmails($errorPages);
                } catch (\Exception $e) {
                    $output->writeln("<error>Notification sending error\n" . $e->getMessage() . "</error>");
                }
            }

            unset($errorPages);
        }

        $db->executeQuery('START TRANSACTION');

        $sql = '
            SELECT
                server_name, hostname, COUNT(*) AS cnt
            FROM
                request
            GROUP BY
                server_name, hostname
        ';

        $servers = $db->fetchAll($sql);

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
        foreach($servers as $server) {
            $sql .= '
                INSERT INTO ipm_report_2_by_hostname_and_server
                    (server_name, hostname, req_time_90, req_time_95, req_time_99, req_time_100,
                     mem_peak_usage_90, mem_peak_usage_95, mem_peak_usage_99, mem_peak_usage_100,
                     cpu_peak_usage_90, cpu_peak_usage_95, cpu_peak_usage_99, cpu_peak_usage_100,
                     doc_size_90, doc_size_95, doc_size_99, doc_size_100)
                SELECT
                    r2.server_name,
                    r2.hostname,
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
                    max(doc_size) as doc_size_100
                FROM
                    request r2
                WHERE
                    r2.server_name = "' . $server['server_name'] . '" and r2.hostname = "' . $server['hostname'] . '"
            ;';
        }
        if ($sql != '')
            $db->query($sql);

        $db->executeQuery('COMMIT');

        $sql = '
            INSERT INTO ipm_report_by_hostname
                (
                    req_count, req_per_sec, req_time_total, req_time_percent, req_time_per_sec,
                    ru_utime_total, ru_utime_percent, ru_utime_per_sec,
                    ru_stime_total, ru_stime_percent, ru_stime_per_sec,
                    traffic_total, traffic_percent, traffic_per_sec,
                    hostname
                )
            SELECT req_count, req_per_sec, req_time_total, req_time_percent, req_time_per_sec,
                    ru_utime_total, ru_utime_percent, ru_utime_per_sec,
                    ru_stime_total, ru_stime_percent, ru_stime_per_sec,
                    traffic_total, traffic_percent, traffic_per_sec,
                    hostname FROM report_by_hostname;

            INSERT INTO ipm_report_by_hostname_and_server
                (
                    req_count, req_per_sec, req_time_total, req_time_percent, req_time_per_sec,
                    ru_utime_total, ru_utime_percent, ru_utime_per_sec,
                    ru_stime_total, ru_stime_percent, ru_stime_per_sec,
                    traffic_total, traffic_percent, traffic_per_sec,
                    hostname, server_name
                )
            SELECT req_count, req_per_sec, req_time_total, req_time_percent, req_time_per_sec,
                    ru_utime_total, ru_utime_percent, ru_utime_per_sec,
                    ru_stime_total, ru_stime_percent, ru_stime_per_sec,
                    traffic_total, traffic_percent, traffic_per_sec,
                    hostname, server_name FROM report_by_hostname_and_server;

            INSERT INTO ipm_report_by_server_name
                (
                    req_count, req_per_sec, req_time_total, req_time_percent, req_time_per_sec,
                    ru_utime_total, ru_utime_percent, ru_utime_per_sec,
                    ru_stime_total, ru_stime_percent, ru_stime_per_sec,
                    traffic_total, traffic_percent, traffic_per_sec,
                    server_name
                )
            SELECT req_count, req_per_sec, req_time_total, req_time_percent, req_time_per_sec,
                    ru_utime_total, ru_utime_percent, ru_utime_per_sec,
                    ru_stime_total, ru_stime_percent, ru_stime_per_sec,
                    traffic_total, traffic_percent, traffic_per_sec,
                    server_name FROM report_by_server_name;
        ';
        $db->query($sql);

        $sql = '
            INSERT INTO
                ipm_status_details (server_name, hostname, script_name, status)
            SELECT
                server_name, hostname, script_name, status
            FROM
                request
            WHERE
                status >= 500
            GROUP BY
                server_name, hostname, script_name
            LIMIT
                25
        ';
        $db->query($sql);

        $sql = '';
        foreach($servers as $server) {
            $maxReqTime = static::DEFAULT_SLOW_REQ_TIME;
            if (isset($this->params['logging']['long_request_time']['global'])) {
                $maxReqTime = $this->params['logging']['long_request_time']['global'];
            }
            if (isset($this->params['logging']['long_request_time'][$server['server_name']])) {
                $maxReqTime = $this->params['logging']['long_request_time'][$server['server_name']];
            }
            $sql .= '
                INSERT INTO ipm_req_time_details
                    (server_name, hostname, script_name, req_time)
                SELECT
                    server_name, hostname, script_name, max(req_time)
                FROM
                    request
                WHERE
                    server_name = "' . $server['server_name'] . '" AND hostname = "' . $server['hostname'] . '" AND req_time > ' . (float)$maxReqTime . '
                GROUP BY
                    server_name, hostname, script_name
                ORDER BY
                    req_time DESC
                LIMIT
                    10
            ;';
        }
        if ($sql != '')
            $db->query($sql);

        $sql = '';
        foreach($servers as $server) {
            $maxMemoryUsage = static::DEFAULT_HEAVY_PAGE_MEMORY;
            if (isset($this->params['logging']['heavy_request']['global'])) {
                $maxMemoryUsage = $this->params['logging']['heavy_request']['global'];
            }
            if (isset($this->params['logging']['heavy_request'][$server['server_name']])) {
                $maxMemoryUsage = $this->params['logging']['heavy_request'][$server['server_name']];
            }

            $sql .= '
                INSERT INTO ipm_mem_peak_usage_details
                    (server_name, hostname, script_name, mem_peak_usage)
                SELECT
                    server_name, hostname, script_name, max(mem_peak_usage)
                FROM
                    request
                WHERE
                    server_name = "' . $server['server_name'] . '" AND hostname = "' . $server['hostname'] . '" AND mem_peak_usage > ' . (int)$maxMemoryUsage . '
                GROUP BY
                    server_name, hostname, script_name
                ORDER BY
                    mem_peak_usage DESC
                LIMIT
                    10
            ;';
        }
        if ($sql != '')
            $db->query($sql);

        $sql = '';
        foreach($servers as $server) {
            $maxCPUUsage = static::DEFAULT_HEAVY_PAGE_CPU;
            if (isset($this->params['logging']['heavy_cpu_request']['global'])) {
               $maxCPUUsage = $this->params['logging']['heavy_cpu_request']['global'];
            }
            if (isset($this->params['logging']['heavy_cpu_request'][$server['server_name']])) {
               $maxCPUUsage = $this->params['logging']['heavy_cpu_request'][$server['server_name']];
            }

            $sql .= '
                  INSERT INTO ipm_cpu_usage_details
                      (server_name, hostname, script_name, cpu_peak_usage)
                  SELECT
                      server_name, hostname, script_name, max(ru_utime)
                  FROM
                      request
                  WHERE
                      server_name = "' . $server['server_name'] . '" AND hostname = "' . $server['hostname'] . '" AND ru_utime > ' . (int)$maxCPUUsage . '
                  GROUP BY
                      server_name, hostname, script_name
                  ORDER BY
                      ru_utime DESC
                  LIMIT
                      10
            ;';
        }
        if ($sql != '')
           $db->query($sql);

        // notification about abrupt drawdown of indicators
        $values = $this->getBorderOutValues($db, $servers);
        $this->sendBorderOutEmails($values);

        $output->writeln('<info>Data are aggregated successfully</info>');

        if (!unlink( __FILE__ . '.lock')) {
            $output->writeln('<error>Error: cannot remove ' . __FILE__ . '.lock file, you must remove it manually and check server settings.</error>');
        }
    }


    protected function getBorderOutValues($db, $servers)
    {
        $d = new \DateTime();
        $di = new \DateInterval(
            isset($this->params['aggregation_period']) ? $this->params['aggregation_period'] : 'P15M'
        );
        //2 aggregations ago
        $d->sub($di);
        $d->sub($di);

        $result = array();
        foreach ($servers as $server) {
            if (!isset($result[$server['server_name']]))
                $result[$server['server_name']] = array(
                    'req_per_sec' => $server['cnt'] / ($di->format('%i') ?: 15) / 60,
                );
        }

        //req_time
        foreach (array('95', '90') as $perc) {
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

            $data = $db->fetchAll($sql, array(
                'created_at' => $d->format('Y-m-d H:i:s')
            ));

            $finalData = array();
            foreach($data as $row) {
                if (isset($result[$row['server_name']])) {
                    $finalData[$row['server_name']][$row['hostname']][] = array(
                        'value' => $row['req_time_' . $perc],
                        'created_at' => $row['created_at'],
                    );
                }
            }
            unset($data);

            $defaultBorder =
                isset($this->params['notification']['border']['req_time']['global']) ?
                $this->params['notification']['border']['req_time']['global'] : static::DEFAULT_REQ_TIME_BORDER;

            foreach ($finalData as $server => $hosts) {
                $border =
                    isset($this->params['notification']['border']['req_time'][$server]) ?
                    $this->params['notification']['border']['req_time'][$server] :
                    $defaultBorder;

                foreach ($hosts as $host => $values) {
                    if (sizeof($values) > 1) {
                        if (
                            $result[$server]['req_per_sec'] >= 0.2 &&
                            (
                                $values[0]['value'] >= $border && $values[1]['value'] < $border ||
                                $values[0]['value'] < $border && $values[1]['value'] >= $border
                            )
                        ) {
                            $result[$server]['req_time_' . $perc][] = array(
                                'status' => $values[0]['value'] < $values[1]['value'] ? 'OK' : 'PROBLEM',
                                'hostname' => $host,
                                'current' => $values[0]['value'],
                                'prev'    => $values[1]['value'],
                                'current_formatted' => number_format($values[0]['value'] * 1000, 0, '.', '') . ' ms',
                                'prev_formatted'    => number_format($values[1]['value'] * 1000, 0, '.', '') . ' ms',
                                'current_date' => $values[0]['created_at'],
                                'prev_date'    => $values[1]['created_at'],
                                'border' => number_format($border * 1000, 0, '.', '') . ' ms',
                            );
                        }
                    }
                }
            }
            unset($finalData);
        }

        foreach ($result as $server => $values) {
            if (sizeof($values) < 2) {
                unset($result[$server]);
            }
            else {
                unset($result[$server]['req_per_sec']);
            }
        }

        return $result;
    }

    private function sendBorderOutEmail($data, $message, $address) {
        if (count($data) > 0) {
            $body = $this->app['twig']->render('drawdown_notification.html.twig', array('data' => $data));
            $message->setBody($body);
            $message->setTo($address);

            $this->sendEmail($message);

            unset($body);
        }
    }

    private function sendBorderOutEmails($data)
    {
        $subject = 'Intaro Pinboard has detected a drawdown of indicators';

        $message = \Swift_Message::newInstance()
            ->setContentType('text/html')
            ->setFrom(isset($this->params['notification']['sender']) ? $this->params['notification']['sender'] : 'noreply@pinboard');

        if (isset($this->params['notification']['global_email'])) {
            $status = array();
            $d = array();
            foreach ($data as $server => $values) {
                if($this->isNotIgnore($server)) {
                    $d[$server] = $values;
                    foreach ($values as $indicator) {
                        foreach ($indicator as $host) {
                            $status[] = $host['status'];
                        }
                    }
                }
            }
            $status = array_unique($status);
            $message->setSubject('[' . implode(', ', $status) . '] ' . $subject);
            $this->sendBorderOutEmail($d, $message, $this->params['notification']['global_email']);
            unset($d);
        }

        if (isset($this->params['notification']['list'])) {
            foreach ($this->params['notification']['list'] as $item) {
                $status = array();
                $d = array();
                foreach ($data as $server => $values) {
                    if ($this->isNotIgnore($server) && preg_match('/' . $item['hosts'] . '/', $server)) {
                        $d[$server] = $values;
                        foreach ($values as $indicator) {
                            foreach ($indicator as $host) {
                                $status[] = $host['status'];
                            }
                        }
                    }
                }
                $status = array_unique($status);
                $message->setSubject('[' . implode(', ', $status) . '] ' . $subject);
                $this->sendBorderOutEmail($d, $message, $item['email']);
                unset($d);
            }
        }

        unset($message);
    }
}