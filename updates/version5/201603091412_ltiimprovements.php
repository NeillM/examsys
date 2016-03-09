<?php

if ($updater_utils->check_version("6.1.0")) {
    if (!$updater_utils->has_updated('rogo1642_ltiimprovements')) {
        $new_lines = array("\$lti_auth_timeout = 9072000; // length of lti authorisation in seconds\n");
        $target_line = '$lti_integration';
        $updater_utils->add_line($string, '$lti_auth_timeout', $new_lines, 91, $cfg_web_root, $target_line);
        $updater_utils->record_update('rogo1642_ltiimprovements');
    }
}