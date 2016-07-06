<?php

if ($updater_utils->check_version("6.2.0")) {
    if (!$updater_utils->has_updated('rogo1829_externalids')) {
        // courses.
        $altersql = "ALTER TABLE courses ADD COLUMN `externalid` varchar(255) NULL DEFAULT NULL";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `courses` ADD UNIQUE INDEX `externalid` (`externalid`)";
        $updater_utils->execute_query($altersql, true);
        // faculty.
        $altersql = "ALTER TABLE faculty ADD COLUMN `externalid` varchar(255) NULL DEFAULT NULL";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `faculty` ADD UNIQUE INDEX `externalid` (`externalid`)";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE faculty ADD COLUMN `code` VARCHAR(30) NULL DEFAULT NULL AFTER `id`";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `faculty` ADD UNIQUE INDEX `code` (`code`)";
        $updater_utils->execute_query($altersql, true);
        // schools.
        $altersql = "ALTER TABLE schools ADD COLUMN `externalid` varchar(255) NULL DEFAULT NULL";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `schools` ADD UNIQUE INDEX `externalid` (`externalid`)";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE schools ADD COLUMN `code` VARCHAR(30) NULL DEFAULT NULL AFTER `id`";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `schools` ADD UNIQUE INDEX `code` (`code`)";
        $updater_utils->execute_query($altersql, true);
        // modules.
        $altersql = "ALTER TABLE modules ADD COLUMN `externalid` varchar(255) NULL DEFAULT NULL";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `modules` ADD UNIQUE INDEX `externalid` (`externalid`)";
        $updater_utils->execute_query($altersql, true);
        
        // New API perms.
        $insertsql = "INSERT INTO permissions (action) VALUES "
            . "('coursemanagement/update'), "
            . "('schoolmanagement/update'), "
            . "('facultymanagement/update'), "
            . "('modulemanagement/update'), "
            . "('usermanagement/update'), "
            . "('assessmentmanagement/update')";
        $updater_utils->execute_query($insertsql, true);
        // Drop perms desc column.
        $altersql = "ALTER TABLE permissions DROP COLUMN description";
        $updater_utils->execute_query($altersql, true);
        // New type column in config type.
        $altersql = "ALTER TABLE `config` ADD COLUMN `type` VARCHAR(10) NULL AFTER `value`";
        $updater_utils->execute_query($altersql, true);
        // Update config table with type.
        $altersql = "UPDATE `config` SET type = '" . Config::JSON . "' WHERE setting = 'timezones' AND component = 'core'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE `config` SET type = '" . Config::JSON . "'' WHERE setting = 'cohort_sizes' AND component = 'core'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE `config` SET type = '" . Config::INTEGER . "'' WHERE setting = 'max_duration' AND component = 'core'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE `config` SET type = '" . Config::INTEGER . "'' WHERE setting = 'max_sittings' AND component = 'core'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE `config` SET type = null WHERE component = 'plugin_ims'";
        $updater_utils->execute_query($altersql, true);
        
        
        $updater_utils->record_update('rogo1829_externalids');
    }
}