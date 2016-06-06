<?php

// Adds missing SELECT permissions to the invigilator user.
if ($updater_utils->check_version("6.1.0") and !$updater_utils->has_updated('inv_perms')) {
  $dbname = $configObject->get('cfg_db_database');
  $cfg_web_host = $configObject->get('cfg_web_host');
  $cfg_db_inv_user = $configObject->get('cfg_db_inv_user');
  $tables = array(
    'properties_reviewers',
    'schools',
    'state',
    'staff_help',
  );
  foreach ($tables as $table) {
    $sql = "GRANT SELECT ON $dbname.$table TO '$cfg_db_inv_user'@'$cfg_web_host'";
    $updater_utils->execute_query($sql, false);
  }
  $updater_utils->record_update('inv_perms');
}
