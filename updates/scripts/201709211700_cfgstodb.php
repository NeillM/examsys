<?php

if ($updater_utils->check_version("6.5.0")) {
  if (!$updater_utils->has_updated('rogo2156')) {
    $configObject = Config::get_instance();
    $configObject->set_setting('misc_company', $configObject->get('cfg_company'), Config::STRING);
    $updater_utils->record_update('rogo2156');
  }
}