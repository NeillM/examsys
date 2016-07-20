<?php

// Modules student update needs runnign in msaller chunks on some db engines.

// Update foreign key.
if (!$updater_utils->has_updated('rogo1481alter_modules_student14')) {
    $altersql = "ALTER TABLE modules_student ADD CONSTRAINT modules_student_fk0 FOREIGN KEY (calendar_year) REFERENCES academic_year(calendar_year)";
    $updater_utils->execute_query($altersql, true);
    $updater_utils->record_update('rogo1481alter_modules_student14');
}