<?php

// Your code here

if ($updater_utils->has_grant($cfg_db_staff_user, 'SELECT', 'standards_setting', $cfg_web_host)) {
    $sql = "REVOKE SELECT ON " . $cfg_db_database . ".standards_setting TO '" . $cfg_db_staff_user . "'@'" . $cfg_web_host . "'";
    $updater_utils->execute_query($sql, true);
}

/*
 *****   NOW UPDATE THE INSTALLER SCRIPT   *****
 */
