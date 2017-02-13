<?php

if ($updater_utils->check_version("6.4.0")) {
    if (!$updater_utils->has_updated('mathjax')) {
        // New configs.
        $configObject = Config::get_instance();
        $configObject->set_db_object($mysqli);
        $configObject->set_setting('paper_mathjax', 1, 'boolean');
        $updater_utils->record_update('mathjax');
    }
}