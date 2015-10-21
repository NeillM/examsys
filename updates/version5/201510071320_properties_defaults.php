<?php

if ($updater_utils->check_version("6.1.0")) {

    if (!$updater_utils->has_updated('rogo1559_properties')) {
        $altersql = "ALTER TABLE `properties`
            CHANGE COLUMN `bgcolor` `bgcolor` VARCHAR(20) NULL DEFAULT 'white' AFTER `paper_postscript`,
            CHANGE COLUMN `fgcolor` `fgcolor` VARCHAR(20) NULL DEFAULT 'black' AFTER `bgcolor`,
            CHANGE COLUMN `themecolor` `themecolor` VARCHAR(20) NULL DEFAULT '#316AC5' AFTER `fgcolor`,
            CHANGE COLUMN `labelcolor` `labelcolor` VARCHAR(20) NULL DEFAULT '#C00000' AFTER `themecolor`,
            CHANGE COLUMN `fullscreen` `fullscreen` ENUM('0','1') NOT NULL DEFAULT '1' AFTER `labelcolor`,
            CHANGE COLUMN `marking` `marking` CHAR(60) NULL DEFAULT '1' AFTER `fullscreen`,
            CHANGE COLUMN `bidirectional` `bidirectional` ENUM('0','1') NOT NULL DEFAULT '1' AFTER `marking`,
            CHANGE COLUMN `pass_mark` `pass_mark` TINYINT(4) NULL DEFAULT '40' AFTER `bidirectional`,
            CHANGE COLUMN `distinction_mark` `distinction_mark` TINYINT(4) NULL DEFAULT '70' AFTER `pass_mark`,
            CHANGE COLUMN `random_mark` `random_mark` FLOAT NULL DEFAULT '0' AFTER `created`,
            CHANGE COLUMN `total_mark` `total_mark` MEDIUMINT(9) NULL DEFAULT '0' AFTER `random_mark`,
            CHANGE COLUMN `display_correct_answer` `display_correct_answer` ENUM('0','1') NULL DEFAULT '1' AFTER `total_mark`,
            CHANGE COLUMN `display_question_mark` `display_question_mark` ENUM('0','1') NULL DEFAULT '1' AFTER `display_correct_answer`,
            CHANGE COLUMN `display_students_response` `display_students_response` ENUM('0','1') NULL DEFAULT '1' AFTER `display_question_mark`,
            CHANGE COLUMN `display_feedback` `display_feedback` ENUM('0','1') NULL DEFAULT '1' AFTER `display_students_response`,
            CHANGE COLUMN `hide_if_unanswered` `hide_if_unanswered` ENUM('0','1') NULL DEFAULT '0' AFTER `display_feedback`,
            CHANGE COLUMN `sound_demo` `sound_demo` ENUM('0','1') NULL DEFAULT '0' AFTER `internal_review_deadline`";
            
        $updater_utils->execute_query($altersql, true);
        $updater_utils->record_update('rogo1559_properties');
    }
}