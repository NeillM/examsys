<?php

if ($updater_utils->check_version("6.1.0")) {
    // Adding Mx to title.
    if (!$updater_utils->has_updated('rogo1584_gender')) {
        $altersql = "ALTER TABLE temp_users CHANGE title title enum('Dr','Miss','Mr','Mrs','Ms','Professor','Mx') default NULL";
        $updater_utils->execute_query($altersql, true);
        $updater_utils->record_update('rogo1584_gender');
    }
}