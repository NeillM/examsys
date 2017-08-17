<?php

if ($updater_utils->check_version("6.5.0")) {
    if (!$updater_utils->has_updated('rogo2080')) {
        $configObject = Config::get_instance();
        $configObject->set_setting('cfg_calc_type', $configObject->get('enhancedcalc_type'), Config::STRING);
        if ($configObject->get('enhancedcalc_type') == 'phpEval') {
          $configObject->set_setting('cfg_calc_settings', array('host' => '', 'port' => '', 'timeout' => ''), Config::ASSOC);
        } else {
          $configObject->set_setting('cfg_calc_settings', $configObject->get('enhancedcalculation'), Config::ASSOC);
        }
        $updater_utils->record_update('rogo2080');
    }
}