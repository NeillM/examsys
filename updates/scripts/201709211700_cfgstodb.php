<?php

if ($updater_utils->check_version("6.5.0")) {
  if (!$updater_utils->has_updated('rogo2156')) {
    $configObject->set_setting('misc_company', $configObject->get('cfg_company'), Config::STRING);
    $configObject->set_setting('system_maintenance_mode', 0, Config::BOOLEAN);
    $sql = "INSERT INTO config (component, setting, value, type) VALUES ('core', 'rogo_version', '" . $configObject->get('rogo_version') . "', '" . Config::VERSION . "')";
    $updater_utils->execute_query($sql, false);
    $sql = "INSERT INTO config (component, setting, value, type) VALUES ('core', 'cfg_summative_mgmt', '" . $configObject->get('cfg_summative_mgmt') . "', '" . Config::BOOLEAN . "')";
    $updater_utils->execute_query($sql, false);
    $updater_utils->record_update('rogo2156');
  }
}