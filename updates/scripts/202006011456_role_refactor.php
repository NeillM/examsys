<?php

if ($updater_utils->check_version('7.2.0')) {
    // Create the new tables.
    if (!$updater_utils->has_updated('rogo_2691-newtables')) {
        $sqlroles = 'CREATE TABLE `roles` (
            `id` int(4) NOT NULL auto_increment,
            `name` varchar(30) NOT NULL,
            `grouping` varchar(30) NOT NULL,
            `group` INT(4) NOT NULL,
            PRIMARY KEY (`id`)
        )';
        $updater_utils->execute_query($sqlroles, false);

        $sqluserroles = 'CREATE TABLE `user_roles` (
            `userID` int(10) unsigned NOT NULL,
            `roleID` int(4) NOT NULL,
            PRIMARY KEY (`userID`, `roleID`),
            FOREIGN KEY user_roles_fk0 (userID) REFERENCES users(id),
            FOREIGN KEY user_roles_fk1 (roleID) REFERENCES roles(id)
        )';
        $updater_utils->execute_query($sqluserroles, false);

        $sqlcreateroles = "INSERT INTO roles (`id`, `name`, `grouping`, `group`) VALUES
            (1, 'SysAdmin', 'staff', 1),
            (2, 'Admin', 'staff', 1),
            (3, 'Staff', 'staff', 1),
            (4, 'Student', 'student', 1),
            (5, 'graduate', 'student', 2),
            (6, 'Internal Reviewer', 'staff', 2),
            (7, 'left', 'staff', 3),
            (8, 'Inactive Staff', 'staff', 4),
            (9, 'Suspended', 'student', 3),
            (10, 'Locked', 'student', 4),
            (11, 'External Examiner', 'staff', 5),
            (12, 'Invigilator', 'staff', 6),
            (13, 'SysCron', 'system', 1),
            (14, 'Standards Setter', 'staff', 1)";
        $updater_utils->execute_query($sqlcreateroles, false);

        // Grant access to new tables.
        $sqlgrantroles_auth = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".roles TO '" . $configObject->get('cfg_db_username') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sqlgrantroles_auth, false);
        $sqlgrantuserroles_auth = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".user_roles TO '" . $configObject->get('cfg_db_username') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sqlgrantuserroles_auth, false);

        $sqlgrantroles_stu = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".roles TO '" . $configObject->get('cfg_db_student_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sqlgrantroles_stu, false);
        $sqlgrantuserroles_stu = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".user_roles TO '" . $configObject->get('cfg_db_student_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sqlgrantuserroles_stu, false);

        $sqlgrantroles_ext = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".roles TO '" . $configObject->get('cfg_db_external_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sqlgrantroles_ext, false);
        $sqlgrantuserroles_ext = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".user_roles TO '" . $configObject->get('cfg_db_external_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sqlgrantuserroles_ext, false);

        $sqlgrantroles_int = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".roles TO '" . $configObject->get('cfg_db_internal_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sqlgrantroles_int, false);
        $sqlgrantuserroles_int = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".user_roles TO '" . $configObject->get('cfg_db_internal_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sqlgrantuserroles_int, false);

        $sqlgrantroles_inv = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".roles TO '" . $configObject->get('cfg_db_inv_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sqlgrantroles_inv, false);
        $sqlgrantuserroles_inv = 'GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".user_roles TO '" . $configObject->get('cfg_db_inv_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sqlgrantuserroles_inv, false);

        $sqlgrantuserroles_web = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $configObject->get('cfg_db_database') . ".user_roles TO '" . $configObject->get('cfg_db_webservice_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sqlgrantuserroles_web, false);

        $sqlgrantuserroles_staff = 'GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $configObject->get('cfg_db_database') . ".user_roles TO '" . $configObject->get('cfg_db_staff_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sqlgrantuserroles_staff, false);

        $updater_utils->record_update('rogo_2691-newtables');
    }

    // Migrate the roles data.
    if (!$updater_utils->has_updated('rogo_2691-migration')) {
        $sql = 'SELECT id, roles FROM users';
        $users = $update_mysqli->prepare($sql);
        $users->execute();
        // Unfortunately we need to buffer the results to avoid out of synch errors,
        // this might take a lot of memory.
        $users->store_result();
        $users->bind_result($id, $roles);
        while ($users->fetch()) {
            try {
                Role::updateRoles($id, explode(',', $roles));
            } catch (InvalidRole $e) {
                // The user has invalid roles so lock them.
                Role::updateRoles($id, ['Locked']);
            }
        }
        $updater_utils->record_update('rogo_2691-migration');
    }

    // Delete the old roles column.
    if (!$updater_utils->has_updated('rogo_2691-cleanup')) {
        $dropsql = 'ALTER TABLE users DROP COLUMN roles';
        $updater_utils->execute_query($dropsql, false);
        $updater_utils->record_update('rogo_2691-cleanup');
    }
}
