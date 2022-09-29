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
    if (!$updater_utils->has_updated('rogo_3062')) {
        if ($configObject->get('cfg_secure_connection')) {
            $protocol = 'https';
        } else {
            $protocol = 'http';
        }

        if (!is_null($cfg_site_address)) {
            // The site address was passed via the update script command line options.
            $site_address = $cfg_site_address;
        } else if (PHP_SAPI == 'cli') {
            // We are running the update from the command line we should prompt for the site address,
            // if the user kills the script here none of updates will have been run in, so restarting
            // it should not cause issues.
            do {
                $address = cli_utils::user_response('Please enter the full url for ExamSys:');
                $url = parse_url($address);

                // Make up the host, with port if specified.
                if (empty($url['port'])) {
                    $host = $url['host'];
                } else {
                    $host = $url['host'] . ':' . $url['port'];
                }

                $path = $url['path'] ?? '/';

                // Verify that the scheme is compatiable with the current ExamSys setting.
                if (!empty($url['scheme']) and $url['scheme'] != $protocol) {
                    if ($protocol == 'https') {
                        cli_utils::prompt('Warning: ExamSys is configured as to use secure connections, overriding your url');
                    } else {
                        cli_utils::prompt('Warning: ExamSys is configured to not use secure connection, overriding your url');
                    }
                }

                // Check that the path is correct.
                if ($path !== $configObject->get('cfg_root_path')) {
                    cli_utils::prompt("Warning: The url does not match the root path, overriding your url'.");
                    $path = $configObject->get('cfg_root_path');
                }

                // Build the address.
                $site_address = $protocol . '://' . $host . $path;

                // Check that the user is happy with the address.
                $confirm = cli_utils::user_response("The url will be set as '$site_address'. Is this correct? (y/n)", ['y', 'n']);
            } while ($confirm != 'y');
        } else {
            // The update is being done via the Web UI.
            $site_address = $protocol . '://' . $_SERVER['HTTP_HOST'] . $configObject->get('cfg_root_path');
        }

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
        $search = '$cfg_site_address';
        $new_lines = '$cfg_site_address = "' . $site_address . '";' . PHP_EOL;
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
