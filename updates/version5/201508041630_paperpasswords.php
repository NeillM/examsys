<?php

// Update properties password field to be 90 characters - to hold encrypted password.
if (!$updater_utils->has_updated('rogo1477_paperpassword')) {
    $altersql = "ALTER TABLE properties CHANGE password password char(90) default NULL";
    $updater_utils->execute_query($altersql, true);
    
    $updater_utils->record_update('rogo1477_paperpassword');
}