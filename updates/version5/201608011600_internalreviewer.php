<?php

if ($updater_utils->check_version("6.2.0")) {

    if (!$updater_utils->has_updated('rogo1876_internalreviwer')) {
        $dbname = $configObject->get('cfg_db_database');
        $cfg_web_host = $configObject->get('cfg_web_host');
        $cfg_db_internal_user = $dbname . '_int';
        $cfg_db_internal_passwd = gen_password(16);
    
        $createsql ="CREATE USER  '" . $cfg_db_internal_user . "'@'" . $cfg_web_host . "' IDENTIFIED BY '" . $cfg_db_internal_passwd . "'";
        $updater_utils->execute_query($createsql, true);
        // Grants
        $grantsql = array();
        $grantsql[] = "GRANT SELECT, INSERT ON " . $dbname . ".help_log TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT ON " . $dbname . ".help_searches TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".keywords_question TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log0 TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log1 TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log2 TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log3 TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log4 TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log4_overall TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log5 TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log_late TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".log_metadata TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".modules TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".modules_staff TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".options TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".papers TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".properties TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".questions TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".question_statuses TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".reference_material TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".reference_modules TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".reference_papers TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $dbname . ".review_comments TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT, INSERT, UPDATE ON " . $dbname . ".review_metadata TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".special_needs TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".std_set TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".std_set_questions TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".staff_help TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".student_help TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT INSERT ON " . $dbname . ".sys_errors TO '" . $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".users TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT INSERT ON " . $dbname . ".access_log TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT INSERT ON " . $dbname . ".denied_log TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".properties_reviewers TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".client_identifiers TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".labs TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".properties_modules TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".log_extra_time TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".log_lab_end_time TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".schools TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".paper_metadata_security TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".modules_student TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".question_exclude TO '" . $cfg_db_internal_user . "'@'" . $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".users_metadata TO '" . $cfg_db_internal_user . "'@'" . $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".marking_override TO '" . $cfg_db_internal_user . "'@'" . $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".sid TO '" . $cfg_db_internal_user . "'@'" . $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".student_notes TO '" . $cfg_db_internal_user . "'@'" . $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".paper_notes TO '" . $cfg_db_internal_user . "'@'" . $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".exam_announcements TO '" . $cfg_db_internal_user . "'@'" . $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".relationships TO '" . $cfg_db_internal_user . "'@'" . $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".feedback_release TO '" . $cfg_db_internal_user . "'@'" . $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".cache_paper_stats TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".paper_feedback TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".objectives TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".sessions TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".academic_year TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
        $grantsql[] = "GRANT SELECT ON " . $dbname . ".config TO '". $cfg_db_internal_user . "'@'". $cfg_web_host . "'";
            
        foreach ($grantsql as $sql) {
            $updater_utils->execute_query($sql, true);
        }
        // Add cron user to config file.
        $new_lines = array("// internal reviwer db user\n","\$cfg_db_internal_user = '$cfg_db_internal_user';\n", "\$cfg_db_internal_passwd = '$cfg_db_internal_passwd';\n");
        $target_line = '$cfg_db_inv_passwd';
        $updater_utils->add_line($string, '$cfg_db_internal_user', $new_lines, 28, $cfg_web_root, $target_line, -2);

        $updater_utils->record_update('rogo1876_internalreviwer');
    }
}