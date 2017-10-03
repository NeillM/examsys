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
    $configObject->set_setting('system_academic_year_start', $configObject->get('cfg_academic_year_start'), Config::STRING);
    $configObject->set_setting('misc_search_leadin_length', $configObject->get('cfg_search_leadin_length'), Config::INTEGER);
    $configObject->set_setting('rpt_percent_decimals', $configObject->get('percent_decimals'), Config::INTEGER);
    $currenthofstee = $configObject->get('hofstee_defaults');
    $configObject->set_setting('stdset_hofstee_pass', array(
        'min_pass'=> $currenthofstee['pass'][0],
        'max_pass' => $currenthofstee['pass'][1],
        'min_fail' => $currenthofstee['pass'][2],
        'max_fail' => $currenthofstee['pass'][3]
        ), Config::ASSOC);
    $configObject->set_setting('stdset_hofstee_distinction', array(
        'min_pass'=> $currenthofstee['distinction'][0],
        'max_pass' => $currenthofstee['distinction'][1],
        'min_fail' => $currenthofstee['distinction'][2],
        'max_fail' => $currenthofstee['distinction'][3]
        ), Config::ASSOC);
    $configObject->set_setting('stdset_hofstee_whole_numbers', $configObject->get('hofstee_whole_numbers'), Config::BOOLEAN);
    $configObject->set_setting('summative_hour_warning', $configObject->get('cfg_hour_warning'), Config::INTEGER);
    $configObject->set_setting('system_install_type', $configObject->get('cfg_install_type'), Config::STRING);
    $sql = "INSERT INTO config (component, setting, value, type) VALUES ('core', 'cfg_ims_enabled', '" . $configObject->get('cfg_ims_enabled') . "', '" . Config::BOOLEAN . "')";
    $updater_utils->execute_query($sql, false);
    $updater_utils->record_update('rogo2156');
  }
}