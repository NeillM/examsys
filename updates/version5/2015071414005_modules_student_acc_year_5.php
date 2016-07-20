<?php

// Modules student update needs runnign in msaller chunks on some db engines.

// Update where calendar_year = 3
if (!$updater_utils->has_updated('rogo1481alter_modules_student4')) {
    $updatesql = "UPDATE modules_student SET calendar_year = 2010 WHERE calendar_year = 3";
    $updater_utils->execute_query($updatesql, true);
    $updater_utils->record_update('rogo1481alter_modules_student4');
}