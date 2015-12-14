<?php

if ($updater_utils->check_version("6.1.0")) {

    // Update properties password field to be 255 characters - to hold encrypted password.
    if (!$updater_utils->has_updated('rogo1559_gradebook')) {
        $createsql = "CREATE TABLE gradebook_paper (
            paperid int(8) NOT NULL,
            timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`paperid`)
        )";
        $updater_utils->execute_query($createsql, true);
        
        $grantsql = "GRANT INSERT ON " . $cfg_db_database . ".gradebook_paper TO '" . $cfg_db_staff_user . "'@'" . $cfg_web_host . "'";
        $updater_utils->execute_query($grantsql, true);
        
        $createsql = "CREATE TABLE gradebook_user (
            paperid int(8) NOT NULL,
            userid int(10) NOT NULL,
            raw_grade int(3) DEFAULT NULL,
            adjusted_grade float DEFAULT NULL,
            classification varchar(50) DEFAULT NULL,
            PRIMARY KEY (paperid, userid)
        )";
        $updater_utils->execute_query($createsql, true);
        
        $altersql = "ALTER TABLE gradebook_user ADD CONSTRAINT gradebook_user_fk0 FOREIGN KEY (paperid) REFERENCES gradebook_paper(paperid)";
        $updater_utils->execute_query($altersql, true);
        
        $grantsql = "GRANT INSERT ON " . $cfg_db_database . ".gradebook_user TO '" . $cfg_db_staff_user . "'@'" . $cfg_web_host . "'";
        $updater_utils->execute_query($grantsql, true);
        
         $insertsql = "INSERT INTO permissions (action, description) VALUES "
            . "('gradebook', 'Gradebook'), "
            . "('assessmentmanagement/create', 'Create/Update an assessment'), "
            . "('assessmentmanagement/schedule', 'SChedule a summative assessment'), "
            . "('assessmentmanagement/delete', 'Delete an assessment')";
        $updater_utils->execute_query($insertsql, true);
                
        $updater_utils->record_update('rogo1559_gradebook');
    }
}
