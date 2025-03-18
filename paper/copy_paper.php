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
 * Copy Paper page - allows copying of exam papers
 *
 * @author Iyud Dissanayake
 * @copyright Copyright (c) 2025 The University of Nottingham
 */

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';

// Get variables from paper_options.php
$paperID = check_var('paperID', 'GET', true, false, true, param::INT);
$module = param::optional('module', null, param::INT, param::FETCH_GET);
$folder = param::optional('folder', null, param::INT, param::FETCH_GET);

// Get paper properties
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

// Initialize the data class and render class
$copyPaperData = new CopyPaperData($string);
$render = new render($configObject);

// Prepare data for the template
$templateData = $copyPaperData->prepareTemplateData(
    $properties,
    $paperID,
    $configObject,
    $mysqli,
    $module,
    $folder
);

// Render the template
$render->render($templateData, $string, 'paper/copy_paper.html');
