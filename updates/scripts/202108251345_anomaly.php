<?php

// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

if ($updater_utils->check_version('7.5.0')) {
    if (!$updater_utils->has_updated('rogo_3062')) {
        // Create anomaly schema.
        $sql = 'CREATE TABLE `anomaly` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `type` tinyint NOT NULL,
            `time` bigint(10) NOT NULL,
            `details` TEXT,
            `userID` int(10) unsigned NOT NULL,
            `paperID` mediumint(8) unsigned NOT NULL,
            `screen` tinyint(3) unsigned DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `anomaly_log_key0` (`type`, `userID`, `paperID`),
            FOREIGN KEY anomaly_log_fk0 (userID) REFERENCES users(id),
            FOREIGN KEY anomaly_log_fk1 (paperID) REFERENCES properties(property_id)
        )';
        $updater_utils->execute_query($sql, false);
        // Grant access to new tables.
        $sqlgrantstu = 'GRANT INSERT ON ' . $configObject->get('cfg_db_database') . ".anomaly TO '"
            . $configObject->get('cfg_db_student_user') . "'@'" . $configObject->get('cfg_web_host') . "'";
        $updater_utils->execute_query($sqlgrantstu, false);
        // Create retention definitions.
        $sqlcreateretention = "INSERT INTO retention (`table`, `days`) VALUES ('anomaly', 365)";
        $updater_utils->execute_query($sqlcreateretention, false);

        // Global on/off setting.
        $papertypes = array(
            'progress' => 0,
            'summative' => 0,
        );
        $configObject->set_setting('paper_anomaly_detection', $papertypes, Config::ASSOC);

        // Email address to send anomaly report to.
        $configObject->set_setting('paper_anomaly_email', array(), Config::EMAIL);

        // Store site address needed by cli email system.
        if ($configObject->get('cfg_secure_connection')) {
            $protocol = 'https';
        } else {
            $protocol = 'http';
        }
        $search = '$cfg_site_address';
        $new_lines = '$cfg_site_address = '
            . '"' . $protocol . '://' . $_SERVER['HTTP_HOST']
            . $configObject->get('cfg_root_path')
            . '";' . PHP_EOL;
        $target_line = '$cfg_tmpdir';
        $updater_utils->add_line($string, $search, $new_lines, -1, $cfg_web_root, $target_line);

        // Create scheduled mail schema.
        $sqlmail = 'CREATE TABLE `scheduledmail` (
            `type` varchar(10) NOT NULL,
            `last` bigint(10),
            PRIMARY KEY (`type`)
        )';
        $updater_utils->execute_query($sqlmail, false);
        // Create scheduled mail schema.
        $sqlmail = 'INSERT INTO scheduledmail VALUES ("anomaly", null)';
        $updater_utils->execute_query($sqlmail, false);
        $updater_utils->record_update('rogo_3062');
    }
}
