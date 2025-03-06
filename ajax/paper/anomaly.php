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
 * Log paper anomaly
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @version 1.0
 * @copyright Copyright (c) 2021 The University of Nottingham
 * @package core
 */

define('AJAX_REQUEST', true);

require_once '../../include/staff_student_auth.inc';

$paperid = param::required('paperID', param::INT, param::FETCH_POST);
$screen = param::required('screen', param::INT, param::FETCH_POST);
$previous = param::required('previous', param::TEXT, param::FETCH_POST);
$current = param::required('current', param::TEXT, param::FETCH_POST);

$properties = PaperProperties::get_paper_properties_by_id($paperid, $mysqli, $string);

if (Anomaly::anomalyDetectionEnabled($properties->get_paper_type())) {
    $data = [
        'userid' => $userObject->get_user_ID(),
        'paperid' => $paperid,
        'screen' => $screen,
        'previous' => $previous,
        'current' => $current,
    ];
    $anomaly = new ClockAnomaly($data);
    $anomaly->insert();
}
