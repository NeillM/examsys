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

// Marking options
define('MARK_NO_ADJUSTMENT', '0');
define('MARK_RANDOM', '1');
define('MARK_STD_SET', '2');

$paperID = check_var('paperID', 'REQUEST', true, false, true);
$module = param::optional('module', null, param::INT, param::FETCH_GET);
$folder = param::optional('folder', null, param::INT, param::FETCH_GET);

$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);

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

$render->render(
    [
        'heading' => $string['changesheading'],
        'class' => 'changes',
    ],
    [],
    'paper/properties_heading.html',
);

?>
<div id="content">
<?php
$modules = module_utils::get_module_list_by_id($mysqli);

$table = new \component\table\Table(
    headings: [$string['part'], $string['old'], $string['new'], $string['date'], $string['author']],
    escape: false,
    highlight: false,
);

$formatter = new PaperChangesFormatter($string);

$logger = new Logger($mysqli);
$changes = $logger->get_changes('Paper', $paperID, $formatter->getLoggerCallbacks());

foreach ($changes as $change) {
    $part = $change['part'];

    [$old, $new] = $formatter->format($part, $change['old'], $change['new']);

    if (isset($string[$part])) {
        $part = $string[$part];
    }

    $table->addRow([
        ucfirst((string) $part),
        $old,
        $new,
        date($configObject->get('cfg_very_short_datetime_php'), $change['date']),
        $change['title'] . ' ' . $change['surname'],
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
