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

/**
 *
 * This script is designed to compare marks between the Class Totals report and students' actual exam scripts (finish.php).
 * It works by:
 *   1. Get summative exam papers in the require date range.
 *   2. For each paper call class_totals.php and parse for student IDs and marks.
 *   3. For each student call finish.php and compare the mark.
 *   4. Echo errors for any which do not match.
 *
 * @author Simon Wilkinson and Joseph Baxter
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require dirname(__DIR__) . '/include/sysadmin_auth.inc';
include_once dirname(__DIR__) . '/include/load_config.php';
require_once 'classes/class_totals.php';
set_time_limit(0);
session_write_close();
$response = '123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890123456789012345678901234567890';
ignore_user_abort(true);
header('Connection: close');
header('Content-Length: ' . strlen($response));
echo $response;
flush();
$now = time();
$end_dateSQL = $now;
$period = param::optional('period', null, param::ALPHANUM, param::FETCH_POST);
if (!is_null($period)) {
    if ($period == 'day') {
        $start_dateSQL = $now - date_utils::DAYSECS;
    } elseif ($period == 'week') {
        $start_dateSQL = $now - date_utils::WEEKSECS;
    } elseif ($period == 'month') {
        $server_timezone = new DateTimeZone($configObject->get('cfg_timezone'));
        $datetime = new DateTime('now', $server_timezone);
        $datetime->sub(new DateInterval('P1M'));
        $start_dateSQL = $datetime->getTimestamp();
    } elseif ($period == 'year') {
        $start_dateSQL = $now - date_utils::YEARSECS;
    } elseif ($period == '2year') {
        $start_dateSQL = $now - (date_utils::YEARSECS * 2);
    } elseif ($period == '3year') {
        $start_dateSQL = $now - (date_utils::YEARSECS * 3);
    } elseif ($period == '6year') {
        $start_dateSQL = $now - (date_utils::YEARSECS * 6);
    }
} else {
    $start_dateSQL = $now - (date_utils::YEARSECS * 5);
}

if (!isset($protocol)) {
    $protocol = 'https://';
}

$userid = $userObject->get_user_ID();
if (isset($_POST['paper']) and $_POST['paper'] != '') {
    $class_totals = new class_totals();
    $class_totals->processPapers($mysqli, $userid, $start_dateSQL, $end_dateSQL, $string, $userObject, $_POST['paper']);
} else {
    $class_totals = new class_totals();
    $class_totals->processPapers($mysqli, $userid, $start_dateSQL, $end_dateSQL, $string, $userObject);
}
