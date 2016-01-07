<?php

if ($updater_utils->check_version("6.1.0")) {
	
	require $cfg_web_root . 'config/campuses.inc';
	
    if (!$updater_utils->has_updated('rogo1605_campusconfig')) {
        // Save json encoded list of campuses.
        $campusarray = array();
		$i = 0;
        foreach ($cfg_campus_list as $value) {
            if ($value == 'cfg_campus_default') {
                $default = true;
            } else {
                $default = false;
            }
            $campusarray[] = array('id' => $i, 'name' => $value, 'default' => $default);
			$i++;
        }
        $encoded_campuses = json_encode($campusarray);
        $configObject = Config::get_instance();
        $configObject->set_db_object($mysqli);
        $configObject->set_setting('campuses', $encoded_campuses);
        $updater_utils->record_update('rogo1605_campusconfig');
    }
}