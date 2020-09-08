<?php
if ($updater_utils->check_version('7.0.0')) {
    if (!$updater_utils->has_updated('rogo2325')) {
        $sql = "UPDATE track_changes SET old = '********', new = '********' WHERE part = 'password'";
        $updater_utils->execute_query($sql, false);
        $updater_utils->record_update('rogo2325');
    }
}
