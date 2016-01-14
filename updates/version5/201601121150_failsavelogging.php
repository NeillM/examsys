<?php

if ($updater_utils->check_version("6.1.0")) {
    if (!$updater_utils->has_updated('rogo1607_addtionalfailsavelogging')) {
        // Truncate table.
        $truncatesql = "TRUNCATE TABLE save_fail_log";
        $updater_utils->execute_query($truncatesql, true);
        // Add new request_url and response_data columns.
        $altersql = "ALTER TABLE save_fail_log
            ADD COLUMN `request_url` VARCHAR(255) NULL DEFAULT NULL,
            ADD COLUMN `response_data` VARCHAR(50) NULL DEFAULT NULL";
        $updater_utils->execute_query($altersql, true);
        $updater_utils->record_update('rogo1607_addtionalfailsavelogging');
    }
}