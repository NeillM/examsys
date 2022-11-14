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
 * This page redirects the user to the lab that an IP address is part of.
 *
 * @copyright Copyright (c) 2022 The University of Nottingham
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 */

require '../include/staff_auth.inc';
require '../include/errors.php';

$address = check_var('ip', 'REQUEST', true, false, true, param::IP_ADDRESS);

$labs = new LabFactory($mysqli);
$lab = $labs->get_lab_from_address($address);

if ($lab === false) {
    display_error($string['nolab'], sprintf($string['nolabdesc'], $address), false, true, false);
}

// Redirect to the lab page.
header('location: ' . $configObject->get('cfg_root_path') . '/admin/lab_details.php?labID=' . $lab, true, 303);
