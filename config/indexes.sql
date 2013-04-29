ALTER TABLE `ipm_report_2_by_hostname_and_server` ADD INDEX `sn_h_c` (`server_name` , `hostname`, `created_at`);
ALTER TABLE `ipm_report_2_by_hostname_and_server` ADD INDEX `sn_c` (`server_name`, `created_at`);
ALTER TABLE `ipm_status_details` ADD INDEX `isd_c` (`created_at`);