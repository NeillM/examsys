<?php

if ($updater_utils->check_version("6.1.0")) {

    if (!$updater_utils->has_updated('rogo1559_wsconfig')) {
        // Save json encoded list of timezones.
        global $timezone_array;
        $encoded_timezones = json_encode($timezone_array);
        $encoded_cohorts = json_encode(array('<whole cohort>', '0-10', '11-20', '21-30', '31-40', '41-50', '51-75', '76-100', '101-150', '151-200', '201-300',
        '301-400', '401-500'));
        $configObject = Config::get_instance();
        $configObject->set_db_object($mysqli);
        $configObject->set_setting('timezones', $encoded_timezones);
        $configObject->set_setting('cohort_sizes', $encoded_cohorts);
        $configObject->set_setting('max_duration', 779);
        $configObject->set_setting('max_sittings', 6);
        $updater_utils->record_update('rogo1559_wsconfig');
    }
}