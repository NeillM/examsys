<?php

if ($updater_utils->check_version("6.2.0")) {
    if (!$updater_utils->has_updated('rogo1883_marks_config')) {
        // New configs.
        $configObject = Config::get_instance();
        $configObject->set_db_object($mysqli);
        $configObject->set_setting('paper_marks_postive', json_encode(range(1, 20)), 'csv');
        $configObject->set_setting('paper_marks_negative', json_encode(array(0, -0.25, -0.5, -1, -2, -3, -4, -5, -6, -7, -8, -9, -10)), 'csv');

        // Update config type.
        $updatesql = "UPDATE config set type = 'csv' WHERE setting = 'summative_cohort_sizes'";
        $updater_utils->execute_query($altersql, true);
        $updater_utils->record_update('rogo1883_marks_config');
    }
}