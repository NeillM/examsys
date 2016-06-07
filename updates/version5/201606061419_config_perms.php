<?php
// Update the select permissions for the config table that is new in Rogo 6.1.0
if ($updater_utils->check_version("6.1.0") and !$updater_utils->has_updated('config_perms')) {
  // Database details.
  $dbname = $configObject->get('cfg_db_database');
  $cfg_web_host = $configObject->get('cfg_web_host');
  // User details.
  $cfg_db_inv_user = $configObject->get('cfg_db_inv_user');
  $cfg_db_sct_user = $configObject->get('cfg_db_sct_user');
  $cfg_db_external_user = $configObject->get('cfg_db_external_user');
  $cfg_db_student_user = $configObject->get('cfg_db_student_user');
  $cfg_db_username = $configObject->get('cfg_db_username');
  // We do not need to add select to the staff, web or sys users as they get it across the whole schema.
  $permissions = array(
    "GRANT SELECT ON $dbname.config TO '$cfg_db_inv_user'@'$cfg_web_host'",
    "GRANT SELECT ON $dbname.config TO '$cfg_db_sct_user'@'$cfg_web_host'",
    "GRANT SELECT ON $dbname.config TO '$cfg_db_external_user'@'$cfg_web_host'",
    "GRANT SELECT ON $dbname.config TO '$cfg_db_student_user'@'$cfg_web_host'",
    "GRANT SELECT ON $dbname.config TO '$cfg_db_username'@'$cfg_web_host'",
  );
  foreach ($permissions as $permission) {
    $updater_utils->execute_query($permission, false);
  }
  $updater_utils->record_update('config_perms');
}
