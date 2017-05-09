<?php

// Config no longer required in 6.4.0 onwards so do not run this update script on that version or version higher.
$version = $configObject->getxml('version');
if (version::is_version_higher($version, '6.4.0') === false and $version !== '6.4.0') {
  $new_lines = array("\$cfg_tablesorter_date_time = 'uk';\r\n");
  $target_line = '$cfg_long_date_time ';
  $updater_utils->add_line($string, '$cfg_tablesorter_date_time', $new_lines, 69, $cfg_web_root, $target_line, 1);
}