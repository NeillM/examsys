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

require '../include/staff_auth.inc';
require '../include/errors.php';
require '../lang/' . $language . '/paper/properties.php';

$modules = param::optional('modules', '', param::TEXT);

// We need to sanitise the modules to prevent SQL injection attacks.
$module_list = explode(',', (string) $modules);
$sanitised_modules = param::clean_array($module_list, param::INT);
$modules = implode(',', $sanitised_modules);

$renderer = new Render($configObject);

$ref_line = 0;
// Get the current metadata settings for the paper
$current_settings = [];
$stmt = $mysqli->prepare('SELECT refID FROM reference_papers WHERE paperID = ?');
$stmt->bind_param('i', $_GET['paperID']);
$stmt->execute();
$stmt->store_result();
$stmt->bind_result($current_refID);
while ($stmt->fetch()) {
    $current_settings[$current_refID] = $current_refID;
}
$stmt->close();
// Get the dropdown list values
if ($modules != '') {
    $sql = <<<SQL
        SELECT DISTINCT title, reference_material.id
        FROM reference_material, reference_modules, modules
        WHERE reference_material.id = reference_modules.refID AND reference_material.deleted IS NULL
            AND reference_modules.idMod = modules.id AND modules.id IN ({$modules})
        GROUP BY reference_material.id
    SQL;

    $stmt = $mysqli->prepare($sql);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($title, $refID);
    $ref_line = 0;

    if ($stmt->num_rows() > 0) {
        while ($stmt->fetch()) {
            $checkbox = new \component\form\Checkbox(
                id: 'ref' . $ref_line,
                name: 'ref' . $ref_line,
                label: $title,
                value: $refID,
                checked: isset($current_settings[$refID]),
            );
            $renderer->renderComponent($checkbox);

            $ref_line++;
        }
    } else {
        $text = new \component\form\StaticHtml($string['nomaterials']);
        $renderer->renderComponent($text);
    }
    $stmt->close();
}

$metacount = new Hidden(
    id: 'reference_no',
    name: 'reference_no',
    value: $ref_line,
);
$renderer->renderComponent($metacount);
