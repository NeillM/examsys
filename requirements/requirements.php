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
 *
 * Check that system requirements are met before updating.
 * As we may not have twig we cannot use templates in this file.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2017 The University of Nottingham
 */

require_once '../include/load_config.php';

if (is_null($configObject->get('cfg_web_root'))) {
  require_once '../include/path_functions.inc.php';
  $cfg_web_root = get_root_path() . '/';
  $configObject->set('cfg_web_root', $cfg_web_root);
}
$language = LangUtils::getLang($cfg_web_root);

// Install lang packs if not installed.
$langpackfound = 0;
if(!LangUtils::langPackInstalled($language)) {
  try {
    InstallUtils::download_langpacks();
    $langpackfound = 1;
  } catch (Exception $e) {
    $langpackfound = 2;
  }
}

LangUtils::loadlangfile(str_replace($cfg_web_root, '', str_replace('\\', '/', ($_SERVER['SCRIPT_FILENAME']))));

$php_min_ver = $configObject->getxml('php', 'min_version');
$phpversion = requirements::check_php_version();
$phpext = requirements::check_php_extensions();
$phpallext = true;
foreach ($phpext as $idx => $val) {
    if (!$val) {
      $phpallext = false;
    }
}

// Lang packs.
if ($langpackfound === 1) {
  $info['langpacks'] = array($string['langpacksfound'], false);
} elseif ($langpackfound === 2) {
  $info['langpacks'] = array(sprintf($string['langpacksmissing'], $language), false);
}
// php version.
if (!$phpversion) {
  $info['phpversion'] = array(sprintf($string['phpversion'],$php_min_ver), false);
} else {
  $info['phpversion'] = array($string['phpsuccess'],true);
}
// db version.
if (InstallUtils::config_exists()){
  $mysql_min_ver = $configObject->getxml('database', 'mysql', 'min_version');
  $dbversion = requirements::check_db($configObject->get('cfg_db_host'), $configObject->get('cfg_db_username'), $configObject->get('cfg_db_passwd'));
  if (!$dbversion) {
    $info['dbversion'] = array(sprintf($string['dbversion'], $mysql_min_ver), false);
  } else {
    $info['dbversion'] = array($string['dbsuccess'], true);
  }
} else {
  // On install skip check here as done in insall process.
  $dbversion = true;
}
// php extensions.
foreach ($phpext as $idx => $val) {
    if (!$val) {
      $blurb = sprintf($string['phpextension'], $idx);
      $info[$idx] = array($blurb ,false);
    } else {
      $blurb = sprintf($string['phpextensionsuccess'], $idx);
      $info[$idx] = array($blurb ,true);
    }
}

$html = <<<HTML
  <div class="requirements-header">
    <div class="requirements-body-item">Requirement</div>
    <div class="requirements-body-item">Passed?</div>
  </div>
HTML;
echo $html;
foreach ($info as $idx => $val) {
  echo "<div class=\"requirements-body\"><div class=\"requirements-body-item\">$val[0]</div><div class=\"requirements-body-item\">";
  if ($val[1]) {
    echo "<img src=\"../artwork/tick.png\" id=\"yes\" /></div>";
  }elseif ($idx === 'langpacks') {
    echo "<img src=\"../artwork/exclamation.png\" id=\"warn\" /></div>";
  } else {
    echo "<img src=\"../artwork/cross.png\" id=\"no\" /></div>";
  }
  echo "</div>";
}
echo "<div class=\"requirements-body\">";
if ($phpversion and $phpallext and $dbversion) {
  if (InstallUtils::config_exists()){
    echo "<button id=\"update\" class=\"updatebutton\" onclick=\"run_update()\">Update</button>";
  } else {
    echo "<button id=\"install\" class=\"updatebutton\" onclick=\"run_install()\">Install</button>";
  }
} else {
  echo "<p>" . $string['help'] . "</p>";
}
echo "</div>";