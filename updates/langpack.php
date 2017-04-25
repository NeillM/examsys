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

// Start class autoloading.
require_once dirname(__DIR__) . '/include/autoload.inc.php';
autoloader::init();

$error = '';
if (isset($_POST['download'])) {
    if ($_POST['download'] == 1) {
        $error = InstallUtils::download_langpacks(0);
        if ($error === '') {
            header("location: version5.php", true, 303);
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <meta http-equiv="content-type" content="text/html;charset={{charset}}"/>
        <title>"Rog&#333;: Install/Update Language Packs</title>
        <link rel="stylesheet" type="text/css" href="../css/body.css"/>
        <link rel="stylesheet" type="text/css" href="../css/rogo_logo.css"/>
        <link rel="stylesheet" type="text/css" href="../css/header.css"/>
        <link rel="stylesheet" type="text/css" href="../css/updater.css"/>
        <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
        <script type="text/javascript" src="../updates/js/langpack.min.js"></script>
    </head>
    <body>
        <div align="center">
            <form id="theform" name="langpacks" method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>" autocomplete="off">
                <table class="header">
                    <tr>
                      <th style="padding-top:4px; padding-bottom:4px; padding-left:16px">
                          <img src="../artwork/r_logo.gif" alt="logo" class="logo_img" />
                          <div class="logo_lrg_txt">Rog&#333;</div>
                          <div class="logo_small_txt">Install/Update Language Packs</div>
                      </th>
                      <th style="text-align:right; padding-right:10px"><img src="../artwork/software_64.png" width="64" height="64" alt="Rogo Upgrade" /></th>
                    </tr>
                </table>
                <p>To update in a non English langauge you need to install langauge packs. Click OK to download and install all language packs.</p>
                <input type="submit" class="ok" name="submit" value="OK">
                <input type="hidden" name="download" id="download" value="1">
                <p><?php echo $error; ?></p>
            </form>
        </div>
    </body>
</html>