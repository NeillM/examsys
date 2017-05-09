<?php

$new_lines = array("\$cfg_search_leadin_length = 160;\r\n");
$target_line = '$cfg_timezone ';
$updater_utils->add_line($string, '$cfg_search_leadin_length', $new_lines, 64, $cfg_web_root, $target_line, 1);
