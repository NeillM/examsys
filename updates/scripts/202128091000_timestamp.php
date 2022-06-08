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

/**
 * Changes the database so that the extra time column can store a value of more than 128%
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2021 The University of Nottingham
 */

if ($updater_utils->check_version('7.5.0')) {
    if (!$updater_utils->has_updated('rogo_2839')) {
        // Fix some bad data/time out of range.
        $fixsql = "UPDATE properties
            SET
            end_date = '1000-01-01 00:00:00'
            WHERE
            date_format(end_date, '%Y-%m-%d %H:%i:%s') = '0000-00-00 00:00:00'";
        $updater_utils->execute_query($fixsql, false, true);
        $fixsql = "UPDATE properties
            SET
            start_date = '1000-01-01 00:00:00'
            WHERE
            date_format(start_date, '%Y-%m-%d %H:%i:%s') = '0000-00-00 00:00:00'";
        $updater_utils->execute_query($fixsql, false, true);
        $fixsql = "UPDATE properties
            SET
            created = '1000-01-01 00:00:00'
            WHERE
            date_format(created, '%Y-%m-%d %H:%i:%s') = '0000-00-00 00:00:00'";
        $updater_utils->execute_query($fixsql, false, true);
        $fixsql = "UPDATE properties
            SET
            retired = '1000-01-01 00:00:00'
            WHERE
            date_format(retired, '%Y-%m-%d %H:%i:%s') = '0000-00-00 00:00:00'";
        $updater_utils->execute_query($fixsql, false, true);
        // We need to fix any bad dates here or the alter will break even though we are not
        // changing them in stract mode on date values.
        $fixsql = "UPDATE properties
            SET
            internal_review_deadline = '1000-01-01'
            WHERE
            date_format(internal_review_deadline, '%Y-%m-%d') = '0000-00-00'";
        $updater_utils->execute_query($fixsql, false, true);
        $fixsql = "UPDATE properties
            SET
            external_review_deadline = '1000-01-01'
            WHERE
            date_format(external_review_deadline, '%Y-%m-%d') = '0000-00-00'";
        $updater_utils->execute_query($fixsql, false, true);

        // Create temporary table
        $tempsql = 'CREATE TEMPORARY TABLE `properties_dates` (
            `property_id` mediumint(8) unsigned NOT NULL,
            `start_date` bigint(10),
            `end_date` bigint(10),
            `deleted` bigint(10),
            `created` bigint(10),
            `retired` bigint(10)
        )';
        $updater_utils->execute_query($tempsql, false, true);

        // Prepare the query that will insert data,
        // this needs to be done before we start fetching the data to avoid an out of sync error.
        $insertsql = 'INSERT INTO properties_dates VALUES (?, ?, ?, ?, ?, ?)';
        $insertquery = $updater_utils->prepare_query($insertsql);
        $insertquery->bind_param('iiiiii', $insertid, $insertstart, $insertend, $insertdeleted, $insertcreated, $insertretired);

        // Migrate data in temp table.
        $sql = "SELECT
                    property_id,
                    date_format(start_date, '%Y-%m-%d %H:%i:%s'),
                    date_format(end_date, '%Y-%m-%d %H:%i:%s'),
                    date_format(deleted, '%Y-%m-%d %H:%i:%s'),
                    date_format(created, '%Y-%m-%d %H:%i:%s'),
                    date_format(retired, '%Y-%m-%d %H:%i:%s'),
                    timezone
                FROM properties";
        $query = $updater_utils->prepare_query($sql);
        $query->bind_result($property_id, $start_date, $end_date, $deleted, $created, $retired, $timezone);
        $query->execute();

        while ($query->fetch()) {
            $insertid = $property_id;
            $format = 'Y-m-d H:i:s';
            $tz = new DateTimeZone($timezone);
            $insertstart = is_null($start_date) ? null : DateTime::createFromFormat($format, $start_date, $tz)->getTimestamp();
            $insertend = is_null($end_date) ? null : DateTime::createFromFormat($format, $end_date, $tz)->getTimestamp();
            $insertdeleted = is_null($deleted) ? null : DateTime::createFromFormat($format, $deleted, $tz)->getTimestamp();
            $insertcreated = is_null($created) ? null : DateTime::createFromFormat($format, $created, $tz)->getTimestamp();
            $insertretired = is_null($retired) ? null : DateTime::createFromFormat($format, $retired, $tz)->getTimestamp();
            $insertquery->execute();
        }

        $insertquery->close();
        $query->close();

        // Alter schema.
        $altersql = 'ALTER TABLE properties
            MODIFY COLUMN `start_date` bigint(10) default NULL,
            MODIFY COLUMN `end_date` bigint(10) default NULL,
            MODIFY COLUMN `deleted` bigint(10) default NULL,
            MODIFY COLUMN `created` bigint(10) default NULL,
            MODIFY COLUMN `retired` bigint(10) default NULL';
        $updater_utils->execute_query($altersql, false);

        // Update data.
        $updatesql = 'UPDATE
            properties p
        INNER JOIN
            properties_dates d ON p.property_id = d.property_id
        SET
            p.start_date = d.start_date,
            p.end_date = d.end_date,
            p.deleted = d.deleted,
            p.created = d.created,
            p.retired = d.retired';
        $updater_utils->execute_query($updatesql, false);

        // Drop temporary table.
        $dropsql = 'DROP TEMPORARY TABLE `properties_dates`';
        $updater_utils->execute_query($dropsql, false);

        // Add new datetime config setting
        $search = '$cfg_long_full_datetime_php';
        $new_lines = '$cfg_long_full_datetime_php = "d/m/Y H:i";' . PHP_EOL;
        $target_line = '$cfg_short_time_php';
        $updater_utils->add_line($string, $search, $new_lines, -1, $cfg_web_root, $target_line);

        $updater_utils->record_update('rogo_2839');
    }
}
