<?php

// Modules student update needs runnign in msaller chunks on some db engines.

// Update where calendar_year = 0
if (!$updater_utils->has_updated('rogo1481alter_modules_student1')) {
    $updatesql = "UPDATE modules_student SET calendar_year = NULL WHERE calendar_year = 0";
    $updater_utils->execute_query($updatesql, true);
    $updater_utils->record_update('rogo1481alter_modules_student1');
}