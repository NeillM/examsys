<?php

if ($updater_utils->check_version("6.1.0")) {

    // Add key to address as searech on on login screen.
    if (!$updater_utils->has_updated('rogo1582_guestlogin')) {
        $altersql = "ALTER TABLE client_identifiers
            ADD INDEX address_idx (address)";
        $updater_utils->execute_query($altersql, true);
        $updater_utils->record_update('rogo1582_guestlogin');
    }
}