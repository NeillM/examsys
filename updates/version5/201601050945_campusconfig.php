<?php

if ($updater_utils->check_version("6.1.0")) {
    if (!$updater_utils->has_updated('rogo1605_campusconfig')) {

        require $cfg_web_root . 'config/campuses.inc';

        $createsql = "CREATE TABLE campus (
            id int(8) NOT NULL AUTO_INCREMENT,
            name VARCHAR(80) NOT NULL UNIQUE,
            isdefault BOOLEAN NOT NULL default false,
            PRIMARY KEY (`id`),
            INDEX `campus_idx` (`name`)
        )";
        $updater_utils->execute_query($createsql, true);
        foreach ($cfg_campus_list as $value) {
            if ($value == $cfg_campus_default) {
                $default = 1;
            } else {
                $default = 0;
            }
            $insertsql = "INSERT INTO campus (name, isdefault) VALUES (\"" . $value . "\"," . $default . ")";
            $updater_utils->execute_query($insertsql, true);
        }
        $updater_utils->record_update('rogo1605_campusconfig');
    }
}

