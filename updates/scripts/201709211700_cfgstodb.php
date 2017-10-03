<?php

if ($updater_utils->check_version("6.5.0")) {
  if (!$updater_utils->has_updated('rogo2156')) {
    $configObject->set_setting('misc_company', $configObject->get('cfg_company'), Config::STRING);
    $configObject->set_setting('system_maintenance_mode', 0, Config::BOOLEAN);
    $sql = "INSERT INTO config (component, setting, value, type) VALUES ('core', 'rogo_version', '" . $configObject->get('rogo_version') . "', '" . Config::VERSION . "')";
    $updater_utils->execute_query($sql, false);
    $sql = "INSERT INTO config (component, setting, value, type) VALUES ('core', 'cfg_summative_mgmt', '" . $configObject->get('cfg_summative_mgmt') . "', '" . Config::BOOLEAN . "')";
    $updater_utils->execute_query($sql, false);
    $hostname = true;
    if ($configObject->get('cfg_client_lookup') === 'ipaddress') {
      $hostname = false;
    }
    $configObject->set_setting('system_hostname_lookup', $hostname, Config::BOOLEAN);
    $configObject->set_setting('system_academic_year_start', $configObject->get_setting('core', 'cfg_academic_year_start'), Config::STRING);
    $configObject->set_setting('misc_search_leadin_length', $configObject->get_setting('core', 'cfg_search_leadin_length'), Config::INTEGER);
    $updater_utils->record_update('rogo2156');
  }
}