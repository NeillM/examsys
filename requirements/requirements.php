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
 * As we may not have twig or language packs we cannot use templates and translations in this file.
 *
 * @author Dr Joseph Baxter <joseph.baxter@nottingham.ac.uk>
 * @copyright Copyright (c) 2017 The University of Nottingham
 */
require_once '../include/load_config.php';

$language = LangUtils::getLang($cfg_web_root);
LangUtils::loadlangfile(str_replace($cfg_web_root, '', str_replace('\\', '/', ($_SERVER['SCRIPT_FILENAME']))));
$configObject = Config::get_instance();
$php_min_ver = $configObject->getxml('php', 'min_version');
$phpversion = requirements::check_php_version();
$phpext = requirements::check_php_extensions();
$mysql_min_ver = $configObject->getxml('database', 'mysql', 'min_version');
$dbversion = requirements::check_db();
$phpallext = true;
foreach ($phpext as $idx => $val) {
    if (!$val) {
      $phpallext = false;
    }
}

// php version.
if (!$phpversion) {
  $info['phpversion'] = array(sprintf($string['phpversion'],$php_min_ver), false);
} else {
  $info['phpversion'] = array($string['phpsuccess'],true);
}
// db version.
if (!$dbversion) {
  $info['dbversion'] = array(sprintf($string['dbversion'], $mysql_min_ver), false);
} else {
  $info['dbversion'] = array($string['dbsuccess'], true);
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
// Install composer and dependencies.
$composer = requirements::check_composer();
if ($composer === true) {
  $info['composer'] = array($string['composersuccess'], true);
} else {
  $info['composer'] = array($composer, false);
}
// Install npm dependencies.
$npm = requirements::check_npm();
if ($npm === true) {
  $info['npm'] = array($string['npmsuccess'], true);
} else {
  $info['npm'] = array($npm, false);
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
    echo "<img src=\"../artwork/tick.gif\" id=\"yes\" /></div>";
  } else {
    echo "<img src=\"../artwork/cross.gif\" id=\"no\" /></div>";
  }
  echo "</div>";
}
echo "<div class=\"requirements-body\">";
if ($phpversion and $phpallext and $composer) {
  if (InstallUtils::config_exists()){
    echo "<button id=\"update\" class=\"updatebutton\" onclick=\"run_update()\">Update</button>";
  } else {
    echo "<button id=\"install\" class=\"updatebutton\" onclick=\"run_install()\">Install</button>";
  }
} else {
  echo "<p>" . $string['help'] . "</p>";
}
echo "</div>";