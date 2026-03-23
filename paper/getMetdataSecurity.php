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
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

use component\form\Hidden;
use component\form\Select;

require '../include/staff_student_auth.inc';
require '../include/errors.php';
$paperID = check_var('paperID', 'GET', true, false, true);
$year = param::optional('session', '', param::INT);
$modules = param::optional('modules', '', param::TEXT);

// We need to sanitise the modules to prevent SQL injection attacks.
$module_list = explode(',', (string) $modules);
$sanitised_modules = param::clean_array($module_list, param::INT);
$modules = implode(',', $sanitised_modules);

$renderer = new Render($configObject);

// Get the current metadata settings for the paper
$paper_utils = new PaperUtils();
$current_settings = $paper_utils->get_security_metadata($paperID, $mysqli);
$old_type = '';
$meta_no = 0;
if ($_GET['session'] != '') {
    $sql_session = "AND calendar_year='" . $_GET['session'] . "'";
} else {
    $sql_session = '';
}

// Get the dropdown list values
if ($modules != '') {
    $stmt = $mysqli->prepare('SELECT DISTINCT type, value FROM users_metadata, modules WHERE modules.id = users_metadata.idMod AND modules.id IN (' . $modules . ") $sql_session GROUP BY value, type ORDER BY type, value");
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($type, $value);
    $metadata = [];
    while ($stmt->fetch()) {
        $metadata[$type][$value] = $value;
    }
    $stmt->close();

    foreach ($metadata as $type => $options) {
        $hidden = new Hidden(
            id: 'meta_type' . $meta_no,
            name: 'meta_type' . $meta_no,
            value: $type,
        );
        $select = new Select(
            id: 'meta_value' . $meta_no,
            name: 'meta_value' . $meta_no,
            label: $type,
            options: ['' => '<any>'] + $options,
            default: $current_settings[$type] ?? '',
        );
        $renderer->renderComponent($hidden);
        $renderer->renderComponent($select);
        $meta_no++;
    }
}

$metacount = new Hidden(
    id: 'meta_dropdown_no',
    name: 'meta_dropdown_no',
    value: $meta_no,
);
$renderer->renderComponent($metacount);
