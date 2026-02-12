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
 * Page that displays components available in ExamSys
 *
 * @author Neill Magill <neill.magill@nottingham.ac.uk>
 * @copyright Copyright (c) 2026 The University of Nottingham
 */

require '../include/sysadmin_auth.inc';
require '../include/toprightmenu.inc';

use component\Register;

$collection = param::optional('collection', null, param::ALPHA);
$component = param::optional('component', null, param::ALPHA);

if (isset($collection) && !Register::isCollection($collection)) {
    // The collection name is invalid.
    $error = $string['collectionnotfound'];
    $notice->display_notice_and_exit($mysqli, $error, $error, $error, '../artwork/page_not_found.png', '#C00000', true, true);
}

if ($collection === null) {
    // We cannot load a component when there is no collection.
    $component = null;
}

if (isset($component) && !Register::isComponent($collection, $component)) {
    // The request for the component is invalid.
    $error = $string['componentnotfound'];
    $notice->display_notice_and_exit($mysqli, $error, $error, $error, '../artwork/page_not_found.png', '#C00000', true, true);
}

$renderer = new render($configObject);

$toprightmenu = draw_toprightmenu();

$additionaljs = '';
$addtionalcss = '<link rel="stylesheet" type="text/css" href="/css/componentlibrary.css"/>'
    . \component\Helper::getCSSString();

$breadcrumb = new \component\breadcrumb\Breadcrumb();
$breadcrumb->addBreadcrumb($string['home'], '../index.php');
$breadcrumb->addBreadcrumb($string['administrativetools'], '../admin/index.php');

$lang = [];
// Set the page title, and add any additional breadcrumbs.
if ($component) {
    $lang['title'] = sprintf($string['title'], "{$collection}\\{$component}");
    $breadcrumb->addBreadcrumb($string['component'], 'library.php');
    $breadcrumb->addBreadcrumb($collection, "library.php?collection={$collection}");
    $breadcrumb->addCurrentPage($component);
} else if ($collection) {
    $lang['title'] = sprintf($string['title'], $collection);
    $breadcrumb->addBreadcrumb($string['component'], 'library.php');
    $breadcrumb->addCurrentPage($collection);
} else {
    $breadcrumb->addCurrentPage($string['component']);
    $lang['title'] = $string['component'];
}

$lang = \component\Helper::combineLang($lang, $breadcrumb);

$renderer->render_admin_header($lang, $additionaljs, $addtionalcss);
$renderer->render_admin_options('', '', $string, $toprightmenu, 'admin/options_empty.html');
$renderer->render_admin_content($breadcrumb->getData($renderer), $lang);

if ($component) {
    $data = [
        'collection' => $collection,
        'component' => $component,
        'description' => sprintf($string['example'], "{$collection}/{$component}"),
    ];
    $renderer->render($data, $string, 'component/preview.html');
} else if ($collection) {
    $data = [
        'collection' => $collection,
        'components' => Register::getComponentList($collection),
    ];
    $renderer->render($data, $string, 'component/component_list.html');
} else {
    $data = [
        'collections' => Register::getCollectionList(),
    ];
    $renderer->render($data, $string, 'component/collection_list.html');
}

$renderer->render_admin_footer([]);
