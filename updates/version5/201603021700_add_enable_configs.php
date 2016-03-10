<?php

if ($updater_utils->check_version("6.1.0")) {

    if (!$updater_utils->has_updated('rogo6.1.0_enableconfigs')) {
        // Add cron user to config file.
        $new_lines = array("// gradebook setting\n","\$cfg_gradebook_enabled = true;\n", "// IMS enterprise setting\n","\$cfg_ims_enabled = false;\n"
            , "// API setting\n","\$cfg_api_enabled = true;\n");
        $target_line = '$cfg_oauth_always_issue_new_refresh_token';
        $updater_utils->add_line($string, '$cfg_gradebook_enabled', $new_lines, 189, $cfg_web_root, $target_line);

        $updater_utils->record_update('rogo6.1.0_enableconfigs');
    }
}