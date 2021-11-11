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
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

define('AJAX_REQUEST', true);

require '../include/staff_auth.inc';
require_once '../include/errors.php';

$assessment = new assessment($mysqli, $configObject);
$central_mgmt = $configObject->get_setting('core', 'cfg_summative_mgmt');
$paper_name = check_var('paper_name', 'POST', true, false, true);
$paper_type = check_var('paper_type', 'POST', true, false, true);
$paper_owner = check_var('paper_owner', 'POST', true, false, true);
$session = param::optional('session', null, param::INT, param::FETCH_POST);
if (is_null($session)) {
    $yearutils = new yearutils($mysqli);
    $session = $yearutils->get_current_session();
}

$papertype = $assessment->get_type_value($paper_type);
if ($papertype === false) {
    $errorline = __LINE__ - 2;
    $msg = __FILE__ . ' Line: ' . $errorline . ' Error:' . $string['papertypenotfound'];
    echo json_encode(array('ERROR', $notice->ajax_notice('$paper_type' . $string['papertypenotfound'], $msg)));
    exit();
}
// Process the posted modules
$modules = array();
$first = true;
$moduleno = param::optional('module_no', 0, param::INT, param::FETCH_POST);
for ($i = 0; $i < $moduleno; $i++) {
    $mod = param::optional('mod' . $i, -1, param::INT, param::FETCH_POST);
    if ($mod != -1) {
        if ($first == true) {
            $first_module = $mod;
            $first = false;
        }
        $modules[] = $mod;
    }
}

$timezone = param::optional('timezone', $configObject->get('cfg_timezone'), param::TEXT, param::FETCH_POST);

if ($central_mgmt and $papertype == $assessment::TYPE_SUMMATIVE) {
    $duration = 0;
    $durationhours = param::optional('duration_hours', null, param::INT, param::FETCH_POST);
    $durationmins = param::optional('duration_mins', null, param::INT, param::FETCH_POST);

    if (!is_null(['duration_hours'])) {
        $duration += ($durationhours * 60);
    }
    if (!is_null($durationmins)) {
        $duration += $durationmins;
    }

    $start_date = null;
    $end_date = null;
} else {
    $duration = null;
    $fyear = check_var('fyear', 'POST', true, false, true);
    $fmonth = check_var('fmonth', 'POST', true, false, true);
    $fday = check_var('fday', 'POST', true, false, true);
    $fhour = check_var('fhour', 'POST', true, false, true);
    $fminute = check_var('fminute', 'POST', true, false, true);

    $tyear = check_var('tyear', 'POST', true, false, true);
    $tmonth = check_var('tmonth', 'POST', true, false, true);
    $tday = check_var('tday', 'POST', true, false, true);
    $thour = check_var('thour', 'POST', true, false, true);
    $tminute = check_var('tminute', 'POST', true, false, true);
    if ((bool) date('L', strtotime($fyear))) {
        $leap = true;
    } else {
        $leap = false;
    }

    if ($leap == true and $fmonth == '02' and ($fday == '30' or $fday == '31')) {
        $fday = '29';
    }
    if ($leap == false and $fmonth == '02' and ($fday == '29' or $fday == '30' or $fday == '31')) {
        $fday = '28';
    }
    if (($fmonth == '04' or $fmonth == '06' or $fmonth == '09' or $fmonth == '11') and $fday == '31') {
        $fday = '30';
    }

    $start_date = date_utils::getDateTimeFromSelection($fyear, $fmonth, $fday, $fhour, $fminute, $timezone);

    if ($leap == true and $tmonth == '02' and ($tday == '30' or $tday == '31')) {
        $tday = '29';
    }
    if ($leap == false and $tmonth == '02' and ($tday == '29' or $tday == '30' or $tday == '31')) {
        $tday = '28';
    }
    if (($tmonth == '04' or $tmonth == '06' or $tmonth == '09' or $tmonth == '11') and $tday == '31') {
        $tday = '30';
    }

    $end_date = date_utils::getDateTimeFromSelection($tyear, $tmonth, $tday, $thour, $tminute, $timezone);
}

try {
    $remote = 0;
    if ($papertype == $assessment::TYPE_SUMMATIVE) {
        $remote = check_var('remote_summative', 'POST', false, false, true);
        if (is_null($remote)) {
            $remote = 0;
        } else {
            $remote = 1;
        }
    }

    if (is_null($start_date)) {
        $sdate = null;
    } else {
        $sdate = $start_date->format('Y-m-d H:i:s');
    }
    if (is_null($start_date)) {
        $edate = null;
    } else {
        $edate = $end_date->format('Y-m-d H:i:s');
    }

    $property_id = $assessment->create(
        $paper_name,
        $papertype,
        $paper_owner,
        $sdate,
        $edate,
        '',
        $duration,
        $session,
        $modules,
        $timezone,
        null,
        null,
        $remote
    );

    if ($central_mgmt and $papertype == $assessment::TYPE_SUMMATIVE) {
        $barriers_needed = param::optional('barriers_needed', 0, param::BOOLEAN, param::FETCH_POST);
        $period = param::optional('period', '', param::TEXT, param::FETCH_POST);
        $cohort_size = param::optional('cohort_size', '<whole cohort>', param::TEXT, param::FETCH_POST);
        $notes = param::optional('notes', '', param::TEXT, param::FETCH_POST);
        $sittings = param::optional('sittings', 1, param::INT, param::FETCH_POST);
        $campus = param::optional('campus', '', param::TEXT, param::FETCH_POST);
        $assessment->schedule($property_id, $period, $barriers_needed, $cohort_size, $notes, $sittings, $campus);
    }
} catch (Exception $e) {
    $log = new logger($mysqli);
    // Log warning to system.
    $errorstring = $e->getMessage();
    $errorline = __LINE__ - 14;
    $log->record_application_warning($paper_owner, 'Paper Creation', $errorstring, $_SERVER['PHP_SELF'], $errorline);
    $msg = $errorline . ' Error code: ' . $e->getCode() . ' - ' . $errorstring;
    echo json_encode(array(
        'ERROR',
        $notice->ajax_notice($string['errorcreatingpaper'], $msg)
    ));
    exit();
}

if ($property_id !== false) {
    $folder = param::optional('folder', '', param::INT, param::FETCH_POST);
    echo json_encode(array(
        'SUCCESS',
        $property_id,
        $first_module,
        $folder
    ));
} else {
    $log = new logger($mysqli);
    // Log warning to system.
    $errorline = __LINE__ - 33;
    $log->record_application_warning(
        $paper_owner,
        'Paper Creation',
        $string['dbinsertfailed'],
        $_SERVER['PHP_SELF'],
        $errorline
    );
    echo json_encode(array(
        'ERROR',
        $notice->ajax_notice($string['errorcreatingpaper'], $string['dbinsertfailed'])
    ));
}
