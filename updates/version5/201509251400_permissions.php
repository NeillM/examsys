<?php

if ($updater_utils->check_version("6.1.0")) {

    // Update properties password field to be 255 characters - to hold encrypted password.
    if (!$updater_utils->has_updated('rogo1559_permissions')) {
        $createsql = "CREATE TABLE webservice_permissions (
            client_id varchar(80) NOT NULL,
            action varchar(80) NOT NULL,
            access BOOLEAN NOT NULL default false,
            PRIMARY KEY (client_id,action)
        )";
        $updater_utils->execute_query($createsql, true);
        
        $createsql = "CREATE TABLE permissions (
            action varchar(80) NOT NULL,
            description varchar(255) NOT NULL,
            PRIMARY KEY (action)
        )";
        $updater_utils->execute_query($createsql, true);
        
        $insertsql = "INSERT INTO permissions (action, description) VALUES "
            . "('modulemanagement/enrol', 'Enrol Users onto a module'), "
            . "('modulemanagement/unenrol', 'UnEnrol Users from a module'), "
            . "('modulemanagement/create', 'Create/Update a module'), "
            . "('modulemanagement/delete', 'Delete a module'), "
            . "('usermanagement/create', 'Create/Update a user'), "
            . "('usermanagement/delete', 'Delete a user'), "
            . "('coursemanagement/create', 'Create/Update a course'), "
            . "('coursemanagement/delete', 'Delete a course'), "
            . "('schoolmanagement/create', 'Create/Update a school'), "
            . "('schoolmanagement/delete', 'Delete a school'), "
            . "('facultymanagement/create', 'Create/Update a faculty'), "
            . "('facultymanagement/delete', 'Delete a faculty')";
        $updater_utils->execute_query($insertsql, true);
        
        $updater_utils->record_update('rogo1559_permissions');
    }
}