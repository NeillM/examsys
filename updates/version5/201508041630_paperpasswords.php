<?php

// Update properties password field to be 90 characters - to hold encrypted password.
if (!$updater_utils->has_updated('rogo1477_paperpassword')) {
    $altersql = "ALTER TABLE properties CHANGE password password char(90) default NULL";
    $updater_utils->execute_query($altersql, true);
    
    // Encrypt existing paper passwords.
    $select = $mysqli->prepare("SELECT property_id, password FROM properties WHERE password != ''");
    $select->execute();
    $select->store_result();
    $select->bind_result($property_id, $password);
    
    $salt = UserUtils::get_salt();
    while ($select->fetch()) {
        $encpassword = encpw($salt, $property_id, $property_id . $password);
        $updatesql = $mysqli->prepare ("UPDATE properties set password = ? WHERE property_id = ?");
        $updatesql->bind_param('si', $encpassword, $property_id);
        $updatesql->execute();
        $updatesql->close();
    }
    $select->close();
    
    $updater_utils->record_update('rogo1477_paperpassword');
}