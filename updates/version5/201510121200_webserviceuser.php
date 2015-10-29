<?php

if ($updater_utils->check_version("6.1.0")) {

    if (!$updater_utils->has_updated('rogo1559_webserviceuser')) {
        $cfg_db_webservice_user = $cfg_db_database . '_web';
        $cfg_db_webservice_passwd = gen_password(16);
    
        $createsql ="CREATE USER  '" . $cfg_db_webservice_user . "'@'" . $cfg_db_host . "' IDENTIFIED BY '" . $cfg_db_webservice_passwd . "'";
        $updater_utils->execute_query($createsql, true);
        // Grants
        $grantsql[] = "GRANT SELECT ON " . $cfg_db_database . ".* TO '" . $cfg_db_webservice_user . "'@'" . $cfg_db_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".faculty TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".schools TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".courses TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".modules_student TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".modules TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT ON " . $dbname . ".modules_staff TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".users TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".sid TO '". self::$cfg_db_webservice_user . "'@'". self::$cfg_web_host . "'";
        foreach ($grantsql as $sql) {
            $updater_utils->execute_query($sql, true);
        }
        // Add cron user to config file.
        $new_lines = array("// web service db user\n","\$cfg_db_webservice_user = '$cfg_db_webservice_user';\n", "\$cfg_db_webservice_passwd = '$cfg_db_webservice_passwd';\n");
        $target_line = '$cfg_db_inv_passwd';
        $updater_utils->add_line($string, '$cfg_db_webservice_user', $new_lines, 28, $cfg_web_root, $target_line, -2);

        $updater_utils->record_update('rogo1559_webserviceuser');
    }
}