<?php

if ($updater_utils->check_version("6.1.0")) {

    if (!$updater_utils->has_updated('rogo1559_timezones')) {
        // Save json encoded list of timezones.
        global $timezone_array;
        $encoded_timezones = json_encode($timezone_array);
        $configObject = \Config::get_instance();
        $configObject->set_db_object($mysqli);
        $configObject->set_setting('timezones', $encoded_timezones);
    }
}