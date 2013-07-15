<?php

// Add 'warn' column to status
if (!$updater_utils->does_column_exist('question_statuses', 'display_warning')) {
  $updater_utils->execute_query("ALTER TABLE question_statuses ADD COLUMN display_warning tinyint(3) DEFAULT 0 AFTER validate", true);
}


/*
 *****   NOW UPDATE THE INSTALLER SCRIPT   *****
 */