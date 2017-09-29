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
$composer = requirements::check_composer();
$npm = requirements::check_npm();
// php version.
$blurb = "PHP version " . $php_min_ver . " or above is required";
if (!$phpversion) {
  $info['phpversion'] = array($blurb ,false);
} else {
  $info['phpversion'] = array($blurb ,true);
}
// db version.
$blurb = "MYSQL version " . $mysql_min_ver . " or above is required";
if (!$dbversion) {
  $info['dbversion'] = array($blurb ,false);
} else {
  $info['dbversion'] = array($blurb ,true);
}
// php extensions.
foreach ($phpext as $idx => $val) {
    $blurb = "The PHP extension " . $idx . " is required";
    if (!$val) {
      $info[$idx] = array($blurb ,false);
    } else {
      $info[$idx] = array($blurb ,true);
    }
}
// Install composer and dependencies.
$blurb = "Composer and its library dependencies it supplies are required";
if (!$composer) {
  $info['composer'] = array($blurb ,false);
} else {
  $info['composer'] = array($blurb ,true);
}
// Install npm dependencies.
$blurb = "NPM is required";
if (!$npm) {
  $info['npm'] = array($blurb ,false);
} else {
  $info['npm'] = array($blurb ,true);
}
$html = <<<HTML
  <div class="requirements-header">
    <div class="requirements-body-item">Requirement</div>
    <div class="requirements-body-item">Found?</div>
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
  echo "<p>Rog&#333; was unable to resolve all issues. Please refer to the <a href = \"https://rogo-eassessment-docs.atlassian.net\">documentation</a> on how to install any missing requirements</p>";
}
echo "</div>";