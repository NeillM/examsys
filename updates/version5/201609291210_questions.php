<?php

if ($updater_utils->check_version("6.2.0") and !$updater_utils->has_updated('rogo1971_questions')) {

    // Make reference column same type as referred column.
    $altersql = "ALTER TABLE `questions`
        ALTER `status` DROP DEFAULT";
    $updater_utils->execute_query($altersql, true);

    // Add fk.
    $altersql = "ALTER TABLE `questions`
        CHANGE COLUMN `status` `status` INT(11) NOT NULL AFTER `std`,
        ADD CONSTRAINT `questions_fk1` FOREIGN KEY (`status`) REFERENCES `question_statuses` (`id`)";
    $updater_utils->execute_query($altersql, true);

    $updater_utils->record_update('rogo1971_questions');
}
