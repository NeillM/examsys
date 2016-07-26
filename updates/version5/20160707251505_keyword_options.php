<?php

if ($updater_utils->check_version("6.2.0") and !$updater_utils->has_updated('rogo1866_keyword_options')) {
    // Create new table to store keyword block references.
    $createsql = "CREATE TABLE `keywords_option` (
        `o_id` INT(4) NOT NULL,
        `keyword_id` INT(11) NOT NULL,
        PRIMARY KEY (`o_id`, `keyword_id`),
        CONSTRAINT `keywords_options_fk1` FOREIGN KEY (`o_id`) REFERENCES `options` (`o_id`),
        CONSTRAINT `keywords_options_fk2` FOREIGN KEY (`keyword_id`) REFERENCES `keywords_user` (`id`)
    )";
    $updater_utils->execute_query($createsql, true);
    
    // Update grants.
    $cfg_db_student_user = $cfg_db_database . '_stu';
    $cfg_db_staff_user = $cfg_db_database . '_staff';
    $cfg_db_external_user = $cfg_db_database . '_ext';
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".keywords_option TO '". $cfg_db_student_user . "'@'". $cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT ON " . $cfg_db_database . ".keywords_option TO '" . $cfg_db_external_user . "'@'". $cfg_web_host . "'";
    $priv_SQL[] = "GRANT SELECT, INSERT, UPDATE, DELETE ON " . $cfg_db_database . ".keywords_option TO '". $cfg_db_staff_user . "'@'". $cfg_web_host . "'";
    
    // Migrate existing references.
    $sql = $mysqli->prepare("SELECT o_id, option_text FROM options, questions WHERE q_id = o_id AND q_type = 'keyword_based'");
    $sql->execute();
    $sql->store_result();
    $sql->bind_result($o_id, $option_text);
    while ($sql->fetch()) {
        $updatesql = "UPDATE options SET option_text = NULL WHERE o_id = $o_id";
        $updater_utils->execute_query($updatesql, true);
        // Some cases of empty option_text (keyword not selected) so do not need to migrate these.
        if (!empty($option_text)) {
            $insertsql = "INSERT INTO keywords_option (o_id, keyword_id) VALUES ($o_id, $option_text)";
            $updater_utils->execute_query($insertsql, true);
        }
    }
    $sql->close();
    $updater_utils->record_update('rogo1866_keyword_options');
}