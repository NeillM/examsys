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


require '../lang/' . $language . '/include/paper_types.php';

$string['title'] = 'Anomaly Report';
$string['clock'] = 'User System Clock Anomaly';
$string['unknown'] = 'Unknown Anomaly';
$string['timestamp'] = 'Time';
$string['name'] = 'Name';
$string['sid'] = 'Student ID';
$string['type'] = 'Type';
$string['screen'] = 'Screen';
$string['details'] = 'Details';
$string['noanomalies'] = 'No anomalies detected';
$string['anomalies'] = 'Anomalies detected';
$string['anomalieskey1'] = 'User System Clock Anomaly: '
    . 'It has been detected that the students clock on their device has altered unexpectedly. '
    . 'This could have led them to gaining an unfair advantage. Please check with the inviglator or system administrator.';
$string['anomalyaccessdenied'] = 'Access Denied';
$string['anomalyerrormessage'] = 'This report is not enabled';
