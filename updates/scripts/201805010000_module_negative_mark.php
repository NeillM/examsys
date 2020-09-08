<?php
if ($updater_utils->check_version('7.0.0')) {
    if (!$updater_utils->has_updated('rogo2356')) {
        // Remove NULL from the neg_marking column. It will only be present in newer installs on the default modules.
        $sql = 'UPDATE modules SET neg_marking = 0 WHERE neg_marking IS NULL';
        $updater_utils->execute_query($sql, false);
        // Set the default value to be 0.
        $altersql = 'ALTER TABLE modules CHANGE COLUMN neg_marking neg_marking TINYINT(1) NULL DEFAULT 0';
        $updater_utils->execute_query($altersql, false);
        $updater_utils->record_update('rogo2356');
    }
}
