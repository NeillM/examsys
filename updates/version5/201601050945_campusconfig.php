<?php

if ($updater_utils->check_version("6.1.0")) {
    if (!$updater_utils->has_updated('rogo1605_campusconfig')) {

        require $cfg_web_root . 'config/campuses.inc';

        // New campus table.
        $createsql = "CREATE TABLE campus (
            id int(8) NOT NULL AUTO_INCREMENT,
            name VARCHAR(80) NOT NULL UNIQUE,
            isdefault BOOLEAN NOT NULL default false,
            PRIMARY KEY (`id`),
            INDEX `campus_idx` (`name`)
        )";
        $updater_utils->execute_query($createsql, true);
        // Copy campuses.inc info into new table.
        foreach ($cfg_campus_list as $value) {
            if ($value == $cfg_campus_default) {
                $default = 1;
            } else {
                $default = 0;
            }
            $insertsql = "INSERT INTO campus (name, isdefault) VALUES (\"" . $value . "\"," . $default . ")";
            $updater_utils->execute_query($insertsql, true);
        }
        // Alter labs.campus to be a fk reference to the new campus table.
        $campuses = $mysqli->prepare("SELECT id, name FROM campus");
        $campuses->execute();
        $campuses->store_result();
        $campuses->bind_result($campusid, $campusname);
        while ($campuses->fetch()) {
            $update = $mysqli->prepare("UPDATE labs SET campus = ? WHERE campus = ?");
            $update->bind_param('is', $campusid, $campusname);
            $update->execute();
            $update->close();
        }
        $campuses->close();
        $altersql = "ALTER TABLE `labs` CHANGE COLUMN `campus` `campus` int(8) NOT NULL AFTER `name`";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE labs ADD CONSTRAINT labs_fk0 FOREIGN KEY (campus) REFERENCES campus(id)";
        $updater_utils->execute_query($altersql, true);
        
        $updater_utils->record_update('rogo1605_campusconfig');
    }
}

