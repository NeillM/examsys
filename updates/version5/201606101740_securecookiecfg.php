<?php

$new_lines = array("if (\$cfg_secure_connection) {
    ini_set('session.cookie_secure', 1);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
};\r\n");
$target_line = 'cfg_secure_connection ';
$updater_utils->add_line($string, 'session.cookie_secure', $new_lines, 20, $cfg_web_root, $target_line, 1);