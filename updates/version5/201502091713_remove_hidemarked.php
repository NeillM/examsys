<?php

// Delete hidemarked state for all users as no longer required.
if (!$updater_utils->has_updated('removehidemarked')) {
    if ($updater_utils->count_rows("SELECT * from state where state_name = 'hidemarked'") > 0) {
        $sql = "delete from state where state_name = 'hidemarked'";
        $updater_utils->execute_query($sql, true);
    }
    $updater_utils->record_update('removehidemarked');
}
?>
