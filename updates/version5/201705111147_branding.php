<?php

if ($updater_utils->check_version("6.4.0")) {
    if (!$updater_utils->has_updated('rogo-2160')) {
        // New configs.
        $configObject = Config::get_instance();
        $configObject->set_db_object($mysqli);
        $configObject->set_setting('logo_main', $configObject->get('cfg_root_path') . '/artwork/logo.png', 'url');
        $configObject->set_setting('logo_email', $server . '/artwork/logo_alt.png', 'url');
        $updater_utils->record_update('rogo-2160');
    }
}