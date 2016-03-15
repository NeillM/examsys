<?php

if ($updater_utils->check_version("6.1.0")) {
    if (!$updater_utils->has_updated('rogo1601_deletebrokenpapers')) {
        // Delete papers with a deleted value of 0000-00-00 00:00:00 as this is no longer checked for on login
        // and new instances will no longer occur.
        $deletesql = "DELETE FROM properties WHERE deleted = '0000-00-00 00:00:00'";
        $updater_utils->execute_query($deletesql, true);
        $updater_utils->record_update('rogo1601_deletebrokenpapers');
    }
}