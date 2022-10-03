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

if ($updater_utils->check_version('7.6.0')) {
    if (!$updater_utils->has_updated('ROGO-2551')) {
        $updater_utils->execute_query('SET foreign_key_checks = 0', false);
        $collation = $configObject->get('cfg_db_collation');
        $charset = $configObject->get('cfg_db_charset');
        $engine = $configObject->get('cfg_db_engine');

        $sql = <<<SQL
            CREATE TABLE `options_metadata` (
                `optionID` INT(11) NOT NULL,
                `type` VARCHAR(255) NOT NULL COLLATE {$collation},
                `value` VARCHAR(2500) NULL DEFAULT NULL COLLATE {$collation},
                PRIMARY KEY (`optionID`, `type`),
                INDEX `options_metadata_fk0` (`optionID`),
                CONSTRAINT `options_metadata_fk0` FOREIGN KEY (`optionID`) REFERENCES `options` (`id_num`)
            ) COLLATE='{$collation}' ENGINE={$engine} DEFAULT CHARSET={$charset}
            SQL;
        $updater_utils->execute_query($sql, false);

        $sql = <<<SQL
            ALTER TABLE `questions_metadata`
                MODIFY COLUMN `value` varchar(2500) DEFAULT NULL COLLATE {$collation},
                MODIFY COLUMN `questionID` INT(4) NOT NULL
            SQL;
        $updater_utils->execute_query($sql, false);

        $updater_utils->execute_query('SET foreign_key_checks = 1', false);

        $updater_utils->execute_query('GRANT SELECT, INSERT, UPDATE, DELETE ON ' . $configObject->get('cfg_db_database') . ".options_metadata TO '" . $configObject->get('cfg_db_staff_user') . "'@'" . $configObject->get('cfg_web_host') . "'", false);
        $updater_utils->execute_query('GRANT SELECT ON ' . $configObject->get('cfg_db_database') . ".options_metadata TO '" . $configObject->get('cfg_db_sct_user') . "'@'"  . $configObject->get('cfg_web_host') . "'", false);

        $updater_utils->record_update('ROGO-2551');
    }
}
