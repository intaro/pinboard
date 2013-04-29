CREATE TABLE IF NOT EXISTS `ipm_info` (
  `req_count` int(11) DEFAULT NULL,
  `time_total` float DEFAULT NULL,
  `ru_utime_total` float DEFAULT NULL,
  `ru_stime_total` float DEFAULT NULL,
  `time_interval` int(11) DEFAULT NULL,
  `kbytes_total` float DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ipm_info';            

CREATE TABLE IF NOT EXISTS `ipm_report_2_by_hostname_and_server` (
  `server_name` varchar(64) DEFAULT NULL,
  `hostname` varchar(32) DEFAULT NULL,
  `req_time_90` float DEFAULT NULL,
  `req_time_95` float DEFAULT NULL,
  `req_time_99` float DEFAULT NULL,
  `req_time_100` float DEFAULT NULL,
  `mem_peak_usage_90` float DEFAULT NULL,
  `mem_peak_usage_95` float DEFAULT NULL,
  `mem_peak_usage_99` float DEFAULT NULL,
  `mem_peak_usage_100` float DEFAULT NULL,
  `doc_size_90` float DEFAULT NULL,
  `doc_size_95` float DEFAULT NULL,
  `doc_size_99` float DEFAULT NULL,
  `doc_size_100` float DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ipm_report_by_hostname` (
  `req_count` int(11) DEFAULT NULL,
  `req_per_sec` float DEFAULT NULL,
  `req_time_total` float DEFAULT NULL,
  `req_time_percent` float DEFAULT NULL,
  `req_time_per_sec` float DEFAULT NULL,
  `ru_utime_total` float DEFAULT NULL,
  `ru_utime_percent` float DEFAULT NULL,
  `ru_utime_per_sec` float DEFAULT NULL,
  `ru_stime_total` float DEFAULT NULL,
  `ru_stime_percent` float DEFAULT NULL,
  `ru_stime_per_sec` float DEFAULT NULL,
  `traffic_total` float DEFAULT NULL,
  `traffic_percent` float DEFAULT NULL,
  `traffic_per_sec` float DEFAULT NULL,
  `hostname` varchar(32) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ipm_report3';

CREATE TABLE IF NOT EXISTS `ipm_report_by_hostname_and_script` (
  `req_count` int(11) DEFAULT NULL,
  `req_per_sec` float DEFAULT NULL,
  `req_time_total` float DEFAULT NULL,
  `req_time_percent` float DEFAULT NULL,
  `req_time_per_sec` float DEFAULT NULL,
  `ru_utime_total` float DEFAULT NULL,
  `ru_utime_percent` float DEFAULT NULL,
  `ru_utime_per_sec` float DEFAULT NULL,
  `ru_stime_total` float DEFAULT NULL,
  `ru_stime_percent` float DEFAULT NULL,
  `ru_stime_per_sec` float DEFAULT NULL,
  `traffic_total` float DEFAULT NULL,
  `traffic_percent` float DEFAULT NULL,
  `traffic_per_sec` float DEFAULT NULL,
  `hostname` varchar(32) DEFAULT NULL,
  `script_name` varchar(128) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP                  
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ipm_report5';

CREATE TABLE IF NOT EXISTS `ipm_report_by_hostname_and_server` (
  `req_count` int(11) DEFAULT NULL,
  `req_per_sec` float DEFAULT NULL,
  `req_time_total` float DEFAULT NULL,
  `req_time_percent` float DEFAULT NULL,
  `req_time_per_sec` float DEFAULT NULL,
  `ru_utime_total` float DEFAULT NULL,
  `ru_utime_percent` float DEFAULT NULL,
  `ru_utime_per_sec` float DEFAULT NULL,
  `ru_stime_total` float DEFAULT NULL,
  `ru_stime_percent` float DEFAULT NULL,
  `ru_stime_per_sec` float DEFAULT NULL,
  `traffic_total` float DEFAULT NULL,
  `traffic_percent` float DEFAULT NULL,
  `traffic_per_sec` float DEFAULT NULL,
  `hostname` varchar(32) DEFAULT NULL,
  `server_name` varchar(64) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ipm_report6';

CREATE TABLE IF NOT EXISTS `ipm_report_by_hostname_server_and_script` (
  `req_count` int(11) DEFAULT NULL,
  `req_per_sec` float DEFAULT NULL,
  `req_time_total` float DEFAULT NULL,
  `req_time_percent` float DEFAULT NULL,
  `req_time_per_sec` float DEFAULT NULL,
  `ru_utime_total` float DEFAULT NULL,
  `ru_utime_percent` float DEFAULT NULL,
  `ru_utime_per_sec` float DEFAULT NULL,
  `ru_stime_total` float DEFAULT NULL,
  `ru_stime_percent` float DEFAULT NULL,
  `ru_stime_per_sec` float DEFAULT NULL,
  `traffic_total` float DEFAULT NULL,
  `traffic_percent` float DEFAULT NULL,
  `traffic_per_sec` float DEFAULT NULL,
  `hostname` varchar(32) DEFAULT NULL,
  `server_name` varchar(64) DEFAULT NULL,
  `script_name` varchar(128) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ipm_report7';

CREATE TABLE IF NOT EXISTS `ipm_report_by_script_name` (
  `req_count` int(11) DEFAULT NULL,
  `req_per_sec` float DEFAULT NULL,
  `req_time_total` float DEFAULT NULL,
  `req_time_percent` float DEFAULT NULL,
  `req_time_per_sec` float DEFAULT NULL,
  `ru_utime_total` float DEFAULT NULL,
  `ru_utime_percent` float DEFAULT NULL,
  `ru_utime_per_sec` float DEFAULT NULL,
  `ru_stime_total` float DEFAULT NULL,
  `ru_stime_percent` float DEFAULT NULL,
  `ru_stime_per_sec` float DEFAULT NULL,
  `traffic_total` float DEFAULT NULL,
  `traffic_percent` float DEFAULT NULL,
  `traffic_per_sec` float DEFAULT NULL,
  `script_name` varchar(128) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ipm_report1';

CREATE TABLE IF NOT EXISTS `ipm_report_by_server_and_script` (
  `req_count` int(11) DEFAULT NULL,
  `req_per_sec` float DEFAULT NULL,
  `req_time_total` float DEFAULT NULL,
  `req_time_percent` float DEFAULT NULL,
  `req_time_per_sec` float DEFAULT NULL,
  `ru_utime_total` float DEFAULT NULL,
  `ru_utime_percent` float DEFAULT NULL,
  `ru_utime_per_sec` float DEFAULT NULL,
  `ru_stime_total` float DEFAULT NULL,
  `ru_stime_percent` float DEFAULT NULL,
  `ru_stime_per_sec` float DEFAULT NULL,
  `traffic_total` float DEFAULT NULL,
  `traffic_percent` float DEFAULT NULL,
  `traffic_per_sec` float DEFAULT NULL,
  `server_name` varchar(64) DEFAULT NULL,
  `script_name` varchar(128) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ipm_report4';

CREATE TABLE IF NOT EXISTS `ipm_report_by_server_name` (
  `req_count` int(11) DEFAULT NULL,
  `req_per_sec` float DEFAULT NULL,
  `req_time_total` float DEFAULT NULL,
  `req_time_percent` float DEFAULT NULL,
  `req_time_per_sec` float DEFAULT NULL,
  `ru_utime_total` float DEFAULT NULL,
  `ru_utime_percent` float DEFAULT NULL,
  `ru_utime_per_sec` float DEFAULT NULL,
  `ru_stime_total` float DEFAULT NULL,
  `ru_stime_percent` float DEFAULT NULL,
  `ru_stime_per_sec` float DEFAULT NULL,
  `traffic_total` float DEFAULT NULL,
  `traffic_percent` float DEFAULT NULL,
  `traffic_per_sec` float DEFAULT NULL,
  `server_name` varchar(64) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ipm_report2';

CREATE TABLE IF NOT EXISTS `ipm_report_status` (
  `req_count` int(11) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `hostname` varchar(32) DEFAULT NULL,
  `server_name` varchar(64) DEFAULT NULL,
  `script_name` varchar(128) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='ipm_report_status';            

CREATE TABLE IF NOT EXISTS `ipm_status_details` (
  `server_name` varchar(128) DEFAULT NULL,
  `hostname` varchar(32) DEFAULT NULL,
  `script_name` varchar(128) DEFAULT NULL,
  `status` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ipm_req_time_details` (
  `server_name` varchar(64) DEFAULT NULL,
  `hostname` varchar(32) DEFAULT NULL,
  `script_name` varchar(128) DEFAULT NULL,
  `req_time` float DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `ipm_mem_peak_usage_details` (
  `server_name` varchar(64) DEFAULT NULL,
  `hostname` varchar(32) DEFAULT NULL,
  `script_name` varchar(128) DEFAULT NULL,
  `mem_peak_usage` float DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
