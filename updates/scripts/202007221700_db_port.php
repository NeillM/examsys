<?php
if ($updater_utils->check_version('7.2.0')) {
    if (!$updater_utils->has_updated('rogo_2805')) {
        $search = '$cfg_db_port';
        $new_lines = '$cfg_db_port = 3306;' . PHP_EOL;
        $target_line = '$cfg_db_host';
        $updater_utils->add_line($string, $search, $new_lines, -1, $cfg_web_root, $target_line);
        $updater_utils->record_update('rogo_2805');
    }
}
