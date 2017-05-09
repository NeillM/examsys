<?php

// Config no longer required in 6.4.0 onwards so do not run this update script on that version or version higher.
$version = $configObject->getxml('version');
if (version::is_version_higher($version, '6.4.0') === false and $version !== '6.4.0') {
  $new_lines = array("\$cfg_search_leadin_length = 160;\r\n");
  $target_line = '$cfg_timezone ';
  $updater_utils->add_line($string, '$cfg_search_leadin_length', $new_lines, 64, $cfg_web_root, $target_line, 1);
}
