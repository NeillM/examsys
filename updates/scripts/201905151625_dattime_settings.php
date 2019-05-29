<?php

if ($updater_utils->check_version("7.1.0")) {
  if (!$updater_utils->has_updated('rogo2401')) {
    // Add the new settings to Rogo.
    $search = '$cfg_long_datetime_php =';
    $targetline = '$cfg_short_date_php =';
    $longdefault = $configObject->get('cfg_long_date_php') . ' ' . $configObject->get('cfg_long_time_php');
    $shortdefault = $configObject->get('cfg_long_date_php') . ' ' . $configObject->get('cfg_short_time_php');
    $veryshortdefault = $configObject->get('cfg_short_date_php') . ' ' . $configObject->get('cfg_short_time_php');
    $newlines = <<<NEW
  \$cfg_long_datetime_php = '$longdefault';
  \$cfg_short_datetime_php = '$shortdefault';
  \$cfg_very_short_datetime_php = '$veryshortdefault';

NEW;
    $updater_utils->add_line($string, $search, $newlines, 59, $root, $targetline);
    $updater_utils->record_update('rogo2401');
  }
}
