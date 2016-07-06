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
        $insertsql = "INSERT INTO permissions (action, description) VALUES "
            . "('coursemanagement/update', '" . $string['permupdatecourse'] . "'), "
            . "('schoolmanagement/update', '" . $string['permupdateschool'] . "'), "
            . "('facultymanagement/update', '" . $string['permupdatefaculty'] . "'), "
            . "('modulemanagement/update', '" . $string['permupdatemodule'] . "'), "
            . "('usermanagement/update', '" . $string['permupdateuser'] . "'), "
            . "('assessmentmanagement/update', '" . $string['permupdateassessment'] . "')";
        $updater_utils->execute_query($insertsql, true);
        $altersql = "UPDATE permissions set description = '" . $string['permcreatecourse'] . "' WHERE action = 'coursemanagement/create'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = '" . $string['permcreateschool'] . "' WHERE action = 'schoolmanagement/create'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = '" . $string['permcreatefaculty'] . "' WHERE action = 'facultymanagement/create'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = '" . $string['permcreateuser'] . "' WHERE action = 'usermanagement/create'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = '" . $string['permcreatemodule'] . "' WHERE action = 'modulemanagement/create'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = '" . $string['permcreateassessment'] . "' WHERE action = 'assessmentmanagement/create'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = '" . $string['permdeletecourse'] . "' WHERE action = 'coursemanagement/delete'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = '" . $string['permdeleteschool'] . "' WHERE action = 'schoolmanagement/delete'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = '" . $string['permdeletefaculty'] . "' WHERE action = 'facultymanagement/delete'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = '" . $string['permdeleteuser'] . "' WHERE action = 'usermanagement/delete'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = '" . $string['permdeletemodule'] . "' WHERE action = 'modulemanagement/delete'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = '" . $string['permdeleteassessment'] . "' WHERE action = 'assessmentmanagement/delete'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = '" . $string['permenrol'] . "' WHERE action = 'modulemanagement/enrol'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = '" . $string['permunenrol'] . "' WHERE action = 'modulemanagement/unenrol'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = '" . $string['permscheduleassessment'] . "' WHERE action = 'assessmentmanagement/schedule'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = '" . $string['permgradebook'] . "' WHERE action = 'gradebook'";
        $updater_utils->execute_query($altersql, true);
        $updater_utils->execute_query($altersql, true);
        // New type column in config type.
        $altersql = "ALTER TABLE `config` ADD COLUMN `type` VARCHAR(10) NULL AFTER `value`";
        $updater_utils->execute_query($altersql, true);
        // Update config table with type.
        $altersql = "UPDATE `config` SET type = 'json' WHERE setting = 'timezones' AND component = 'core'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE `config` SET type = 'json' WHERE setting = 'cohort_sizes' AND component = 'core'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE `config` SET type = 'integer' WHERE setting = 'max_duration' AND component = 'core'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE `config` SET type = 'integer' WHERE setting = 'max_sittings' AND component = 'core'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE `config` SET type = null WHERE component = 'plugin_ims'";
        $updater_utils->execute_query($altersql, true);
        
        
        $updater_utils->record_update('rogo1829_externalids');
    }
}