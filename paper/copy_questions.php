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
 * Copy questions page - selects a paper that questions should be copied from
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';

// Get variables from paper_options.php
$paperID = check_var('paperID', 'GET', true, false, true, param::INT);
$module = param::optional('module', null, param::INT, param::FETCH_GET);
$folder = param::optional('folder', null, param::INT, param::FETCH_GET);

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

// Check if user has permission to edit this paper
$userObject = UserObject::get_instance();
if (!$properties->can_user_edit_paper($userObject)) {
    $contactemail = support::get_email();
    $msg = sprintf($string['furtherassistance'], $contactemail, $contactemail);
    $notice->display_notice_and_exit(
        $mysqli,
        $string['accessdenied'],
        $msg,
        $string['accessdenied'],
        '/artwork/page_not_found.png',
        '#C00000',
        true,
        true
    );
}

$render = new render($configObject);

$headerdata = [
    'css' => [
        '/css/copy_paper.css'
    ],
    'metadata' => [],
    'mathjax' => $configObject->get_setting('core', 'cfg_mathjax_path'),
    'three' => $configObject->get_setting('core', 'cfg_three_path'),
    'editor' => $configObject->get_setting('core', 'cfg_editor_path'),
    'texteditor' => '',
    'scripts' => [],
];
$render->render($headerdata, $string, 'header.html');

$data = [
    'action' => 'copy.php',
    'papertype' => $properties->get_paper_type(),
    'paperid' => $paperID,
    'module' => $module,
    'folder' => $folder,
    'papers' => PaperUtils::get_available_papers(
        $userObject,
        'property_id',
        'desc',
        $properties->get_paper_type(),
        $module
    ),
];
$render->render($data, $string, 'paper/copy_from_paper_menu.html');

$render->render([], $string, 'footer.html');
