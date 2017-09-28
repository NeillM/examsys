<?php

if ($updater_utils->check_version("6.5.0")) {
  if (!$updater_utils->has_updated('rogo2156')) {
    $configObject->set_setting('misc_company', $configObject->get('cfg_company'), Config::STRING);
    $configObject->set_setting('system_maintenance_mode', 0, Config::BOOLEAN);
    $updater_utils->record_update('rogo2156');
  }
}