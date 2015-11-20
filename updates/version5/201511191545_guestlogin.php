<?php

if ($updater_utils->check_version("6.1.0")) {

    if (!$updater_utils->has_updated('rogo1582_guestlogin')) {
        // Add key to address as searech on on login screen.
        $altersql = "ALTER TABLE client_identifiers
            ADD INDEX address_idx (address)";
        $updater_utils->execute_query($altersql, true);
        // Add key to start/end dates as common filter.
        $altersql = "ALTER TABLE properties
            ADD INDEX date_idx (start_date, end_date)";
        $updater_utils->execute_query($altersql, true);
        $updater_utils->record_update('rogo1582_guestlogin');
    }
}