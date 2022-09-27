<?php

// This file is part of ExamSys
//
// ExamSys is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// ExamSys is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with ExamSys.  If not, see <http://www.gnu.org/licenses/>.

if ($updater_utils->check_version('7.5.0') and !$updater_utils->has_updated('ROGO-2566')) {
    // Data integrity fixes
    $sql = <<<'EOF'
    UPDATE `track_changes`
    SET `old` = NULL
    WHERE `type` = 'Copied question' AND `old` = ''
    EOF;
    $updater_utils->execute_query($sql, false, true);

    // Create initial table without indexes for fast initial data insertion
    $sql = <<<'EOF'
    CREATE TABLE `questions_lineage` (
        `questionID` INT(4) NOT NULL,
        `parentID` INT(4) NULL DEFAULT NULL,
        `rootID` INT(4) NOT NULL,
        `changeID` INT(4) NOT NULL
    )
    EOF;
    $updater_utils->execute_query($sql, false);
    $updater_utils->execute_query('GRANT INSERT,SELECT ON ' . $configObject->get('cfg_db_database') . ".questions_lineage TO '" . $configObject->get('cfg_db_staff_user') . "'@'" . $configObject->get('cfg_web_host') . "'", false);

    // Quick lookup table for questionID to minimum change ID
    $sql = <<<'EOF'
    CREATE TEMPORARY TABLE `questions_lineage_min_lookup`
    (
        `questionID` INT(4) UNSIGNED NOT NULL,
        `changeID` INT(4) UNSIGNED NOT NULL
    )
    EOF;
    $updater_utils->execute_query($sql, false, true);

    $sql = <<<'EOF'
    INSERT INTO `questions_lineage_min_lookup`
    (`questionID`, `changeID`)
    SELECT
    `q`.`q_id`, MIN(`tc`.`id`)
    FROM `questions` `q`
    JOIN `track_changes` `tc` ON `q`.`q_id` = `tc`.`typeID`
    GROUP BY `q`.`q_id`
    EOF;
    $questionCount = $updater_utils->execute_query($sql, false, true);

    // Create temporary table without indexes for fast data streaming
    $sql = <<<'EOF'
    CREATE TEMPORARY TABLE `questions_lineage_track_changes`
    (
        `new` INT UNSIGNED NOT NULL,
        `old` INT UNSIGNED NULL,
        `changeID` INT UNSIGNED NOT NULL
    )
    EOF;
    $updater_utils->execute_query($sql, false, true);

    // Stream data in, converting strings to integers
    $sql = <<<'EOF'
    INSERT INTO questions_lineage_track_changes
    SELECT
    q.q_id,
    CASE WHEN `tc`.`type` = 'Copied question' THEN CAST(`tc`.`old` AS UNSIGNED) ELSE NULL END AS old_qid,
    `tc`.`id`
    FROM `questions` `q`
    JOIN `track_changes` `tc` ON `q`.`q_id` = `tc`.`typeID`
    JOIN `questions_lineage_min_lookup` `ql` ON `ql`.`questionID` = `q`.`q_id` AND `tc`.`id` = `ql`.`changeID`
    EOF;
    $updater_utils->execute_query($sql, false, true);

    // Drop lookup table, no longer required
    $updater_utils->execute_query('DROP TEMPORARY TABLE `questions_lineage_min_lookup`', false, true);

    // Add indexes
    $sql = <<<'EOF'
    ALTER TABLE `questions_lineage_track_changes`
    ADD INDEX `new` (`new`),
    ADD INDEX `old` (`old`)
    EOF;
    $updater_utils->execute_query($sql, false, true);

    // Generate roots
    $sql = <<<'EOF'
    INSERT INTO `questions_lineage`
    SELECT `questions`.`q_id`, NULL, `questions`.`q_id`, `questions_lineage_track_changes`.`changeID`
    FROM `questions`
    INNER JOIN `questions_lineage_track_changes` ON `questions`.`q_id` = `questions_lineage_track_changes`.`new`
    WHERE `questions_lineage_track_changes`.`old` IS NULL
    EOF;
    $rows = $updater_utils->execute_query($sql, false, true);

    // Compensate for any broken chains, add replacement root
    $sql = <<<'EOF'
    INSERT INTO `questions_lineage`
    SELECT `questions_lineage_track_changes`.`new`, NULL, `questions_lineage_track_changes`.`new`, `questions_lineage_track_changes`.`changeID`
    FROM `questions_lineage_track_changes`
    LEFT JOIN `questions` ON `questions`.`q_id` = `questions_lineage_track_changes`.`new`
    LEFT JOIN `questions` AS `oldquestions` ON `oldquestions`.`q_id` = `questions_lineage_track_changes`.`old`
    WHERE `questions_lineage_track_changes`.`old` IS NOT NULL
    AND `oldquestions`.`q_id` IS NULL
    AND `questions`.`q_id` IS NOT NULL
    EOF;
    $newRows = $updater_utils->execute_query($sql, false, true);
    $rows += $newRows;

    // Add indexes to questions_lineage as we're going to start referring back to itself
    $sql = <<<'EOF'
    ALTER TABLE `questions_lineage`
    ADD INDEX `questions_lineage_fk0_question` (`questionID`),
    ADD INDEX `questions_lineage_fk1_parent` (`parentID`),
    ADD INDEX `questions_lineage_fk2_root` (`rootID`),
    ADD CONSTRAINT `questions_lineage_fk0_question` FOREIGN KEY (`questionID`) REFERENCES `questions` (`q_id`),
    ADD CONSTRAINT `questions_lineage_fk1_parent` FOREIGN KEY (`parentID`) REFERENCES `questions` (`q_id`),
    ADD CONSTRAINT `questions_lineage_fk2_root` FOREIGN KEY (`rootID`) REFERENCES `questions` (`q_id`)
    EOF;
    $updater_utils->execute_query($sql, false, true);

    // Direct children of top level roots
    $sql = <<<'EOF'
    INSERT INTO `questions_lineage`
    SELECT `questions`.`q_id`, `questions_old`.`q_id`, `questions_old`.`q_id`, `questions_lineage_track_changes`.`changeID`
    FROM `questions`
    LEFT JOIN `questions_lineage_track_changes` ON `questions`.`q_id` = `questions_lineage_track_changes`.`new`
    LEFT JOIN `questions` AS `questions_old` ON `questions_old`.`q_id` = `questions_lineage_track_changes`.`old`
    JOIN `questions_lineage` ON `questions_lineage`.`questionID` = `questions_old`.`q_id`
    EOF;
    $newRows = $updater_utils->execute_query($sql, false, true);
    $rows += $newRows;

    // Second level children
    $sql = <<<'EOF'
    INSERT INTO `questions_lineage`
    SELECT `questions`.`q_id`, `questions_lineage`.`questionID`, `questions_lineage`.`rootID`, `questions_lineage_track_changes`.`changeID`
    FROM `questions`
    LEFT JOIN `questions_lineage_track_changes` ON `questions`.`q_id` = `questions_lineage_track_changes`.`new`
    LEFT JOIN `questions` AS `questions_old` ON `questions_old`.`q_id` = `questions_lineage_track_changes`.`old`
    LEFT JOIN `questions_lineage` ON `questions_lineage`.`questionID` = `questions_old`.`q_id`
    WHERE `questions_lineage`.`parentID` IS NOT NULL
    EOF;
    $newRows = $updater_utils->execute_query($sql, false, true);
    $rows += $newRows;

    // Final phase required either a lot of recursively joining the table to itself, or running
    // a loop. Doing consecutively smaller loops was a lot friendlier on both CPU and RAM.
    $sql = <<<'EOF'
    INSERT INTO `questions_lineage`
    SELECT `questions`.`q_id`, `t1`.`old`, `l2`.`rootID`, `t1`.`changeID` FROM questions
    LEFT JOIN `questions_lineage` AS `l1` ON `questions`.`q_id` = `l1`.`questionID`
    LEFT JOIN `questions_lineage_track_changes` AS `t1` ON `questions`.`q_id` = `t1`.`new`
    JOIN `questions_lineage` AS `l2` ON `t1`.`old` = `l2`.questionID
    WHERE l1.questionID IS NULL
    AND l2.questionID IS NOT NULL
    EOF;

    $count = 2;
    while ($rows < $questionCount && $count < 500) { // Safety check
        $count++;
        $newRows = $updater_utils->execute_query($sql, false, true);
        if ($newRows == 0) {
            break; // Recursion complete
        }
        $rows += $newRows;
        if ($rows > $questionCount) {
            echo '<h1 class="error">WARNING: Lineage entries exceeds total question count! Integrity failure</h1>';
        }
        if ($count >= 500) {
            echo '<h1 class="error">WARNING: Run lineage trace loop more than 500 times, likely database integrity failure!</h1>';
        }
    }

    // Drop temporary table
    $updater_utils->execute_query('DROP TEMPORARY TABLE `questions_lineage_track_changes`', false, false);

    // Add final FK to change ID
    $sql = <<<'EOF'
    ALTER TABLE `questions_lineage`
    ADD INDEX `questions_lineage_fk3_change` (`changeID`),
    ADD CONSTRAINT `questions_lineage_fk3_change` FOREIGN KEY (`changeID`) REFERENCES `track_changes` (`id`)
    EOF;
    $updater_utils->execute_query($sql);

    // Set config settings
    $configObject->set_setting('misc_full_question_history_enable', 0, Config::BOOLEAN);
    $configObject->set_setting('misc_full_question_history_display_limit', 200, Config::INTEGER);

    $updater_utils->record_update('ROGO-2566');
}
