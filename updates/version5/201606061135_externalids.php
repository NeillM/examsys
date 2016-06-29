<?php

if ($updater_utils->check_version("6.1.0")) {
    if (!$updater_utils->has_updated('rogo1829_externalids')) {
        // courses.
        $altersql = "ALTER TABLE courses ADD COLUMN `externalid` varchar(50) NULL DEFAULT NULL";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `courses` ADD UNIQUE INDEX `externalid` (`externalid`)";
        $updater_utils->execute_query($altersql, true);
        // faculty.
        $altersql = "ALTER TABLE faculty ADD COLUMN `externalid` varchar(50) NULL DEFAULT NULL";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `faculty` ADD UNIQUE INDEX `externalid` (`externalid`)";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE faculty ADD COLUMN `code` VARCHAR(30) NULL DEFAULT NULL AFTER `id`";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `faculty` ADD UNIQUE INDEX `code` (`code`)";
        $updater_utils->execute_query($altersql, true);
        // schools.
        $altersql = "ALTER TABLE schools ADD COLUMN `externalid` varchar(50) NULL DEFAULT NULL";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `schools` ADD UNIQUE INDEX `externalid` (`externalid`)";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE schools ADD COLUMN `code` VARCHAR(30) NULL DEFAULT NULL AFTER `id`";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `schools` ADD UNIQUE INDEX `code` (`code`)";
        $updater_utils->execute_query($altersql, true);
        // modules.
        $altersql = "ALTER TABLE modules ADD COLUMN `externalid` varchar(50) NULL DEFAULT NULL";
        $updater_utils->execute_query($altersql, true);
        $altersql = "ALTER TABLE `modules` ADD UNIQUE INDEX `externalid` (`externalid`)";
        $updater_utils->execute_query($altersql, true);
        
        // New API perms.
        $insertsql = "INSERT INTO permissions (action, description) VALUES "
            . "('coursemanagement/update', 'Update a course'), "
            . "('schoolmanagement/update', 'Update a school'), "
            . "('facultymanagement/update', 'Update a faculty'), "
            . "('modulemanagement/update', 'Update a module'), "
            . "('usermanagement/update', 'Update a user'), "
            . "('assessmentmanagement/update', 'Update an assessment')";
        $updater_utils->execute_query($insertsql, true);
        $altersql = "UPDATE permissions set description = 'Create a course' WHERE action = 'coursemanagement/create'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = 'Create a school' WHERE action = 'schoolmanagement/create'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = 'Create a course' WHERE action = 'facultymanagement/create'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = 'Create a user' WHERE action = 'usermanagement/create'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = 'Create a module' WHERE action = 'modulemanagement/create'";
        $updater_utils->execute_query($altersql, true);
        $altersql = "UPDATE permissions set description = 'Create an assessment' WHERE action = 'assessmentmanagement/create'";
        $updater_utils->execute_query($altersql, true);
        
        // New type column in config type.
        $altersql = "ALTER TABLE `config` ADD COLUMN `type` VARCHAR(10) NULL AFTER `value`";
        $updater_utils->execute_query($altersql, true);
        
        $updater_utils->record_update('rogo1829_externalids');
    }
}