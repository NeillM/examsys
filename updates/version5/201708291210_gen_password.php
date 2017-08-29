<?php
if ($updater_utils->check_version("6.5.0")) {
    if (!$updater_utils->has_updated('rogo_1843')) {
        $configObject = Config::get_instance();
        $configObject->set_db_object($mysqli);
        $configObject->set_setting('misc_dictionary_file', '', 'string');
        $updater_utils->record_update('rogo_1843');
    }
}
