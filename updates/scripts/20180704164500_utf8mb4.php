<?php
if ($updater_utils->check_version("7.0.0")) {
  if (!$updater_utils->has_updated('rogo_1813')) {
    $search = '$cfg_db_collation';
    switch ($configObject->get('cfg_db_charset')) {
      case 'utf8mb4':
        $new_lines = '$cfg_db_collation = \'utf8mb4_unicode_ci\';' . PHP_EOL;
        break;
      case 'utf8':
        $new_lines = '$cfg_db_collation = \'utf8_general_ci\';' . PHP_EOL;
        break;
      default:
        $new_lines = '$cfg_db_collation = \'latin1_swedish_ci\';' . PHP_EOL;
    }
    $target_line = '$cfg_db_charset';
    $updater_utils->add_line($string, $search, $new_lines, $default_line, $cfg_web_root, $target_line);
    $updater_utils->record_update('rogo_1813');
  }
}