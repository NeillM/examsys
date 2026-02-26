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
 * Displays the history of changes made to a papers settings.
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */

use component\Helper;

require_once '../include/staff_auth.inc';
require_once '../include/errors.php';

$paperID = check_var('paperID', 'REQUEST', true, false, true);
$module = param::optional('module', null, param::INT, param::FETCH_GET);
$folder = param::optional('folder', null, param::INT, param::FETCH_GET);

/**
 * Define callbacks to be used when retrieving tracked changes
 * @param  array  $changed_reviewers    Array of reviewers referenced in changes
 * @param  array  $changed_labs         Array of labs referenced in changes
 * @return array                        Array of callbacks to be registered with the logger
 */
function setup_change_callbacks(&$changed_reviewers, &$changed_labs)
{
    // Define a closure to populate past reviewer IDs
    $reviewers_cb = function ($old, $new) use (&$changed_reviewers) {
        $old_reviewers = explode(',', $old);
        $new_reviewers = explode(',', $new);

        // Add any reviewers in the current change to the $changed_reviewers array
        foreach ($old_reviewers as $reviewer) {
            if ($reviewer != '') {
                $changed_reviewers[$reviewer] = false;
            }
        }
        foreach ($new_reviewers as $reviewer) {
            if ($reviewer != '') {
                $changed_reviewers[$reviewer] = false;
            }
        }
    };

    // Define a closure to populate past labs
    $labs_cb = function ($old, $new) use (&$changed_labs) {
        $old_labs = explode(',', $old);
        $new_labs = explode(',', $new);

        // Add any labs in the current change to the $changed_labs array
        foreach ($old_labs as $lab) {
            if ($lab != '') {
                $changed_labs[$lab] = false;
            }
        }
        foreach ($new_labs as $lab) {
            if ($lab != '') {
                $changed_labs[$lab] = false;
            }
        }
    };

    // Use the closures for changes
    $callbacks = ['externals' => $reviewers_cb, 'internals' => $reviewers_cb, 'labs' => $labs_cb];

    return $callbacks;
}

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

// Build up a list of all past reviewers and labs for the 'changes' tab
$changed_reviewers = [];
$changed_labs = [];

$logger = new Logger($mysqli);

$change_callbacks = setup_change_callbacks($changed_reviewers, $changed_labs);

// Get the changes to be used later
$changes = $logger->get_changes('Paper', $paperID, $change_callbacks);

// Get the changes to be used later
$changes = $logger->get_changes('Paper', $paperID, $change_callbacks);

$render = new render($configObject);

// Output the html header.
$render->render(
    [
        'css' => [
            '/css/header.css',
            '/css/properties.css',
            '/css/warnings.css',
            Helper::getCSSPath(),
        ],
        'js' => [],
    ],
    [
        'title' => $string['title']
    ],
    'header.html'
);

require '../include/toprightmenu.inc';
echo draw_toprightmenu();

// Output the breadcrumbs.
$breadcrumbData = new BreadcrumbData($string);
$breadcrumb = $breadcrumbData->preparePaperBreadcrumb(
    $paperID,
    $properties,
    $module,
    $folder,
    $string['title'],
);
echo $render->render_admin_navigation($breadcrumb->getData($render));

?>
<h1><?php echo $string['changesheading'] ?></h1>
<div id="content">
<?php
$modules = module_utils::get_module_list_by_id($mysqli);

$user_list = [];
if (count($changed_reviewers) > 0) {
    $reviewer_in = implode(',', array_keys($changed_reviewers));
    $results = $mysqli->prepare("SELECT id, title, surname FROM users WHERE id IN ($reviewer_in)");
    $results->execute();
    $results->bind_result($id, $title, $surname);
    while ($results->fetch()) {
        $user_list[$id] = $title . ' ' . $surname;
    }
    $results->close();
}

$reference_material = [];
$results = $mysqli->prepare('SELECT id, title FROM reference_material');
$results->execute();
$results->bind_result($id, $title);
while ($results->fetch()) {
    $reference_material[$id] = $title;
}
$results->close();

$folders = folder_utils::get_all_folders($mysqli);

$table = new \component\table\Table(
    headings: [$string['part'], $string['old'], $string['new'], $string['date'], $string['author']],
    highlight: false,
);

// Changes retrieved at beginning of file
$rows = count($changes);
for ($i = 0; $i < $rows; $i++) {
    $part = $changes[$i]['part'];

    $old = $changes[$i]['old'];
    $new = $changes[$i]['new'];

    switch ($part) {
        case 'startdate':
        case 'enddate':
            $old = date($configObject->get('cfg_short_datetime_php'), $old);
            $new = date($configObject->get('cfg_short_datetime_php'), $new);
            break;
        case 'folder':
            $old = format_folders($old, $folders);
            $new = format_folders($new, $folders);
            break;
        case 'method':
            $old = format_method($old, $string);
            $new = format_method($new, $string);
            break;
        case 'displaycalculator':
        case 'demosoundclip':
        case 'photos':
        case 'ticks_crosses':
        case 'hideallfeedback':
        case 'textfeedback':
        case 'correctanswerhighlight':
        case 'question_marks':
            $old = format_on_off($old, $string);
            $new = format_on_off($new, $string);
            break;
        case 'externals':
        case 'internals':
            $old = format_user($old, $user_list);
            $new = format_user($new, $user_list);
            break;
        case 'background':
        case 'foreground':
        case 'theme':
        case 'labelsnotes':
            $old = format_color($old);
            $new = format_color($new);
            break;
        case 'referencematerial':
            $old = format_referencematerial($old, $reference_material);
            $new = format_referencematerial($new, $reference_material);
            break;
        case 'display':
            $old = format_display($old, $string);
            $new = format_display($new, $string);
            break;
        case 'navigation':
            $old = format_navigation($old, $string);
            $new = format_navigation($new, $string);
            break;
        case 'review':
            $old = format_review($old, $string);
            $new = format_review($new, $string);
            break;
        case 'passmark':
        case 'distinction':
            $old = format_passmark($old, $string);
            $new = format_passmark($new, $string);
            break;
        case 'labs':
            $old = format_lab($old, $changed_labs);
            $new = format_lab($new, $changed_labs);
            break;
        case 'marking':
            $old = format_marking($old, $string);
            $new = format_marking($new, $string);
            break;
    }

    if (isset($string[$part])) {
        $part = $string[$part];
    }

    $table->addRow([
        ucfirst((string) $part),
        $old,
        $new,
        date($configObject->get('cfg_very_short_datetime_php'), $changes[$i]['date']),
        $changes[$i]['title'] . ', ' . $changes[$i]['surname'],
    ]);
}
$render->renderComponent($table);
?>
</div>
<?php

// Output the footer.
$scripts = Helper::combineJS(
    [
        '/js/paperpropertiesinit.min.js',
    ],
    $breadcrumb->getJavascriptForFooter(),
    $table->getJavascriptForFooter(),
);
$render->render(
    [
        'scripts' => $scripts,
    ],
    $string,
    'footer.html'
);

function format_color($color)
{
    return '<div style="background-color:' . $color . '; border:1px solid #C0C0C0; width:50px; height:15px"></div>';
}

function format_referencematerial($ID, $refID)
{
    if ($ID == '') {
        return '';
    }

    return $refID[$ID];
}

function format_folders($id, $folders)
{
    if ($id == '') {
        return '';
    }

    if (isset($folders[$id])) {
        $formatted_string = str_replace(';', '/', $folders[$id]);
    } else {
        $formatted_string = $id;
    }

    return $formatted_string;
}

function format_user($text, $user_list)
{
    if ($text == '') {
        return '';
    }

    $formatted_string = '';
    $parts = explode(',', (string) $text);
    foreach ($parts as $part) {
        if ($formatted_string == '') {
            $formatted_string = $user_list[$part];
        } else {
            $formatted_string .= ', ' . $user_list[$part];
        }
    }

    return $formatted_string;
}

function format_lab($lab_id, $lab_list)
{
    $formatted_string = '';

    $parts = explode(',', (string) $lab_id);
    foreach ($parts as $part) {
        if (isset($lab_list[$part])) {
            $lab_name = $lab_list[$part];
        } else {
            $lab_name = 'unknown';
        }
        if ($formatted_string == '') {
            $formatted_string = $lab_name;
        } else {
            $formatted_string .= ', ' . $lab_name;
        }
    }

    return $formatted_string;
}

function format_marking($marking, $string)
{
    $marking_string = $marking;

    $marking_type = $marking[0];

    $marking_string = match ((string) $marking_type) {
        MARK_NO_ADJUSTMENT => $string['noadjustment'],
        MARK_RANDOM => $string['calculatrrandommark'],
        MARK_STD_SET => $string['stdset'],
        '3' => $string['overallclass2'],
        '4' => $string['overallclass3'],
        '6' => $string['overallclass4'],
        '7' => $string['overallclass5'],
        default => $marking_string,
    };

    return $marking_string;
}

function format_method($method, $string)
{
    if ($method == '0') {
        return $string['noadjustment'];
    } elseif ($method == '1') {
        return $string['calculatrrandommark'];
    } elseif ($method[0] == '2') {
        return $string['stdset'];
    } elseif ($method == '3') {
        return $string['overallclass2'];
    } elseif ($method == '4') {
        return $string['overallclass3'];
    } elseif ($method == '5') {
        return $string['overallclass1'];
    } elseif ($method == '6') {
        return $string['overallclass4'];
    }
    return '';
}

function format_review($method, $string)
{
    if ($method == '0') {
        return $string['singlereview'];
    } else {
        return $string['allpeerspergroup'];
    }
}

function format_passmark($method, $string)
{
    if ($method == 101) {
        return 'Borderline Method';
    } elseif ($method == 102 or $method == 127) {
        return 'N/A';
    } else {
        return $method . '%';
    }
}

function format_on_off($data, $string)
{
    if ($data == 0) {
        return $string['off'];
    } else {
        return $string['on'];
    }
}

function format_display($data, $string)
{
    if ($data == 0) {
        return $string['windowed'];
    } else {
        return $string['fullscreen'];
    }
}

function format_navigation($data, $string)
{
    if ($data == 0) {
        return $string['unidirectional'];
    } else {
        return $string['bidirectional'];
    }
}
