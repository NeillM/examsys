<?php

// Add colour column to status
if (!$updater_utils->does_column_exist('question_statuses', 'colour')) {
  $updater_utils->execute_query("ALTER TABLE question_statuses ADD COLUMN colour char(7) DEFAULT '#000000' AFTER validate", true);
}

/*
 *****   NOW UPDATE THE INSTALLER SCRIPT   *****
 */