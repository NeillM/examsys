<?php

if (!$updater_utils->has_updated('rogo1607_addtionalfailsavelogging')) {
    // Truncate table.
    $truncatesql = "TRUNCATE TABLE save_fail_log";
    $updater_utils->execute_query($truncatesql, true);
    // Add new request_url column.
    $altersql = "ALTER TABLE save_fail_log
        ADD COLUMN `request_url` VARCHAR(255) NULL DEFAULT NULL";
    $updater_utils->execute_query($altersql, true);
    // Add new response_data column.
    $altersql = "ALTER TABLE save_fail_log
        ADD COLUMN `response_data` VARCHAR(50) NULL DEFAULT NULL";
    $updater_utils->execute_query($altersql, true);
    $updater_utils->record_update('rogo1607_addtionalfailsavelogging');
}
