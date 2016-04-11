<?php

if ($updater_utils->check_version("6.1.0")) {

    if (!$updater_utils->has_updated('rogo1559_webserviceuser')) {
        $cfg_db_webservice_user = $cfg_db_database . '_web';
        $cfg_db_webservice_passwd = gen_password(16);
    
        $createsql ="CREATE USER  '" . $cfg_db_webservice_user . "'@'" . $cfg_web_host . "' IDENTIFIED BY '" . $cfg_db_webservice_passwd . "'";
        $updater_utils->execute_query($createsql, true);
        // Grants
        $grantsql = array();
        $grantsql[] = "GRANT SELECT ON " . $cfg_db_database . ".* TO '" . $cfg_db_webservice_user . "'@'" . $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".faculty TO '". $cfg_db_webservice_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".schools TO '". $cfg_db_webservice_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".courses TO '". $cfg_db_webservice_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".modules_student TO '". $cfg_db_webservice_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".modules TO '". $cfg_db_webservice_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT ON " . $cfg_db_database . ".modules_staff TO '". $cfg_db_webservice_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".users TO '". $cfg_db_webservice_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $cfg_db_database . ".sid TO '". $cfg_db_webservice_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".properties TO '". $cfg_db_webservice_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".properties_modules TO '". $cfg_db_webservice_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".scheduling TO '". $cfg_db_webservice_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT INSERT ON " . $cfg_db_database . ".track_changes TO '". $cfg_db_webservice_user . "'@'". $cfg_web_host . "'";
        
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