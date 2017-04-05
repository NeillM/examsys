<?php
// This file is part of Rogō
//
// Rogō is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Rogō is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Rogō.  If not, see <http://www.gnu.org/licenses/>.

/**
* Update/Install Language packs.
* 
* @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
* @copyright Copyright (c) 2017 onwards The University of Nottingham
*/

require '../include/sysadmin_auth.inc';

$render = new render($configObject);
$headerdata = array('css' => array (
    "/css/rogo_logo.css",
    "/css/header.css",
    "/css/updater.css"
),
    'scripts' => array(
    "/js/jquery-1.11.1.min.js",
    "/updates/js/langpack.min.js"
));
$data = array('action' => $_SERVER['PHP_SELF'] . '?download=1', 'error' => '');
$lang['title'] = 'Install/Update Language Packs';
$lang['blurb'] = 'To update in a non English langauge you need to install langauge packs. Click OK to download and install all language packs.';
$lang['linkalt'] = 'Downloading..';
$lang['linkdesc'] = 'Download Language Packs';
$lang['iconalt'] = 'Rogo Upgrade';
$render->render($headerdata, $lang, 'header.html');

$download = param::optional('download', 0, param::BOOLEAN, param::FETCH_GET);

if ($download) {
    $data['error'] = InstallUtils::download_langpacks(0);
    if ($data['error'] === '') {
        header("location: version5.php", true, 303);
        exit();
    }
}
$render->render($data, $lang, 'update/langpack.html');
$render->render_admin_footer();