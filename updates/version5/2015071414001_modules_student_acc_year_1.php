<?php

// Modules student update needs runnign in msaller chunks on some db engines.

// Update calendar_year type.
if (!$updater_utils->has_updated('rogo1481alter_modules_student')) {
    $altersql = "ALTER TABLE modules_student CHANGE calendar_year calendar_year INT(4)";
    $updater_utils->execute_query($altersql, true);
    $updater_utils->record_update('rogo1481alter_modules_student');
}