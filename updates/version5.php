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
 * Update script updates any V5 Rogō to latest V5 Rogō.
 *
 * @author Simon Wilkinson
 * @version 1.0
 * @copyright Copyright (c) 2014 The University of Nottingham
 * @package
 */

require_once '../include/load_config.php';

$language = LangUtils::getLang($cfg_web_root);
// If supported lang pack not installed install them.
if(!LangUtils::langPackInstalled($language)) {
    InstallUtils::download_langpacks();
}

require_once '../include/auth.inc';
require_once '../include/errors.php';
require_once '../include/std_set_shared_functions.inc';
require_once '../include/timezones.php';
require_once dirname(__DIR__) . '/lang/' . $language . '/install/index.php';
require_once dirname(__DIR__) . '/lang/' . $language . '/updates/version5.php';

// Get the code version.
$version = $configObject->getxml('version');
$migration_path = 'version5';

set_time_limit(0);

// Get the installed version.
$old_version = $configObject->get_setting('core', 'rogo_version');
?>
<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

    <title>Rog&#333; <?php echo $configObject->get_setting('core', 'rogo_version') . ' to ' . $version; ?> update Script</title>

    <link rel="stylesheet" type="text/css" href="../css/body.css"/>
		<link rel="stylesheet" type="text/css" href="../css/rogo_logo.css" />
    <link rel="stylesheet" type="text/css" href="../css/header.css"/>
    <link rel="stylesheet" type="text/css" href="../css/updater.css"/>

    <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript" src="../js/jquery.validate.min.js"></script>
  </head>
  <body>
  <table class="header">
    <tr>
      <th style="padding-top:4px; padding-bottom:4px; padding-left:16px">
          <img src="../artwork/r_logo.gif" alt="logo" class="logo_img" />

          <div class="logo_lrg_txt">Rog&#333;</div>
          <div class="logo_small_txt">Update Utility (<?php echo $old_version . ' to ' . $version; ?>)</div>
      </th>
      <th style="text-align:right; padding-right:10px"><img src="../artwork/software_64.png" width="64" height="64" alt="Upgrade Icon" /></th>
    </tr>
  </table>
<?php
if ($updater_utils->check_version("6.4.0")) {
  echo "<p style=\"margin-left:10px\">Rog&#333; $old_version is installed.<br /><br />Please updgrade to version 6.4.0 before proceeding with this upgrade.</p>";
  exit;
}
if (!isset($_POST['update'])) {
  InstallUtils::checkSoftware();
  ?>
<script>
  $(document).ready(function () {
    $("#installForm").validate();
  });

  $(document).ready(function () {
    $('#useLdap').change(function () {
      $('#ldapOptions').toggle();
    });
  });
</script>
  <?php
  if (!InstallUtils::configFileIsWriteable()) {
    ?>
    <h2><?php echo $string['updatefromversion'] . ' ' . $configObject->get_setting('core', 'rogo_version') . ' to ' . $version; ?></h2>
    <div><?php echo $string['warning1']; ?></div>
    <div><?php echo $string['warning2']; ?></div>
    <?php
  } elseif (!InstallUtils::configPathIsWriteable()) {
    ?>
    <h2><?php echo $string['updatefromversion'] . ' ' . $configObject->get_setting('core', 'rogo_version') . ' to ' . $version; ?></h2>
    <div><?php echo $string['warning3']; ?></div>
    <div><?php echo $string['warning4']; ?></div>
    <?php
  } else {
    ?>
  <form id="installForm" class="cmxform" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>" autocomplete="off">
      <div><?php printf($string['msg1'], $version); ?></div>
      <table class="h">
          <tr>
              <td>
                  <nobr><?php echo $string['databaseadminuser']; ?></nobr>
              </td>
              <td class="line">
                  <hr/>
              </td>
          </tr>
      </table>
      <div><?php echo $string['msg2']; ?></div>
      <br/>

      <div><label for="mysql_admin_user"><?php echo $string['dbusername']; ?></label> <input type="text" value="" name="mysql_admin_user" class="required" minlength="2"  autocomplete="off"/></div>
      <div><label for="mysql_admin_pass"><?php echo $string['dbpassword']; ?></label> <input type="password" value="" name="mysql_admin_pass" autocomplete="off"/>
      </div>

      <table class="h">
          <tr>
              <td>
                  <nobr><?php echo $string['onlinehelpsystems']; ?></nobr>
              </td>
              <td class="line">
                  <hr />
              </td>
          </tr>
      </table>
      <div><label for="update_staff_help"><?php echo $string['updatestaffhelp']; ?></label> <input type="checkbox" name="update_staff_help" checked="checked" /></div>
      <div><label for="update_student_help"><?php echo $string['updatestudenthelp']; ?></label> <input type="checkbox" name="update_student_help" checked="checked" /></div>
      <table class="h">
          <tr>
              <td>
                  <nobr><?php echo $string['translationpacks']; ?></nobr>
              </td>
              <td class="line">
                  <hr />
              </td>
          </tr>
      </table>
      <div><label for="update_translationpack"><?php echo $string['updatetranslationpack']; ?></label> <input type="checkbox" name="update_translationpack" /></div>
      <div class="submit"><input type="submit" name="update" value="<?php echo $string['startupdate']; ?>" class="ok" /></div>
  </form>
    <?php
  }
  ?>
   </body>
   </html>
  <?php

} else {
  if ($configObject->get('cfg_db_charset') == null) {
    $cfg_db_charset = 'latin1';
  } else {
    $cfg_db_charset = $configObject->get('cfg_db_charset');
  }

  $mysql_admin_user = param::required('mysql_admin_user', param::TEXT, param::FETCH_POST);
  $mysql_admin_pass = param::required('mysql_admin_pass', param::TEXT, param::FETCH_POST);
  $mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $mysql_admin_user, $mysql_admin_pass, $configObject->get('cfg_db_database'), $cfg_db_charset, $notice, $configObject->get('dbclass'), $configObject->get('cfg_db_port'));

  if ($mysqli->connect_error) {
    echo "<div>Failed to contect to MySQL using " . $mysql_admin_user . '</div>';
    echo "</body>";
    echo "</html>";
    exit;
  }

  // Set db object in config.
  $configObject->set_db_object($mysqli);

  $updater_utils = new UpdaterUtils($mysqli, $configObject->get('cfg_db_database'));

  // Backup the config file before proceeding.
  $updater_utils->backup_file($cfg_web_root, $old_version);

  // Avoid repeated method calls
  $cfg_db_database      = $configObject->get('cfg_db_database');
  $cfg_db_student_user  = $configObject->get('cfg_db_student_user');
  $cfg_db_staff_user    = $configObject->get('cfg_db_staff_user');
  $cfg_db_host          = $configObject->get('cfg_db_host');
  $cfg_db_username      = $configObject->get('cfg_db_username');
  $cfg_db_external_user = $configObject->get('cfg_db_external_user');
  $cfg_db_inv_username  = $configObject->get('cfg_db_inv_user');
  $cfg_use_ldap         = $configObject->get('cfg_use_ldap');

  $cfg_web_host         = $configObject->get('cfg_web_host');
  if ($cfg_web_host == '') {
    $cfg_web_host = $cfg_db_host;
  }

  ob_start();
  
  echo "\n<blockquote>\n<h1>" . $string['startingupdate'] . "</h1>";
  echo "<div>Starting at " . date("H:i:s") . "</div>\n<ol>";
  ob_flush();
  flush();

  $mysqli->autocommit(false);

  /*
   *****   ALL UPDATES SHOULD NOW BE PLACED IN DATESTAMPED FILES IN THE version5 FOLDER   *****
   *
   */

  // Run individual update files
  $files = scandir($migration_path);
  foreach ($files as $file) {
    if (StringUtils::ends_with($file, '.php')) {
      include $migration_path . '/' . $file;
      $mysqli->commit();
    }
  }

  $mysqli->commit();
  
  // 01/05/2013 - Update the online help files.
  $update_staff_help = param::optional('update_staff_help', false, param::BOOLEAN, param::FETCH_POST);
  if (!is_null($update_staff_help)) {
    $updater_utils->execute_query("TRUNCATE staff_help", true);

    $file = file_get_contents('../install/staff_help.sql');
    $mysqli->multi_query($file);
    if ($mysqli->error) {
      echo $string['showerror'] . "<br />";
      exit();
    }
    $ext = '';
    while ($mysqli->more_results()) {
      $mysqli->next_result();
      if ($mysqli->insert_id > 0) $ext = $ext . ' ' . $mysqli->insert_id;
    }
    // Ensure all help images are in the correct location.
    $staffhelp = rogo_directory::get_directory('help_staff');
    $staffhelp->create();
    $staffhelp->copy_from_default();
    // Fix path of help file images as may not be in root web dir.
    InstallUtils::correct_staff_path();
    echo "<li>LOADED staff_help: " . $ext . "</li>\n";
  }

  $update_student_help = param::optional('update_student_help', false, param::BOOLEAN, param::FETCH_POST);
  if ($update_student_help) {
    $updater_utils->execute_query("TRUNCATE student_help", true);

    $file = file_get_contents('../install/student_help.sql');
    $mysqli->multi_query($file);
    if ($mysqli->error) {
      echo $string['showerror'] . "<br />";
      exit();
    }
    $ext = '';
    while ($mysqli->more_results()) {
      $mysqli->next_result();
      if ($mysqli->insert_id > 0) $ext = $ext . ' ' . $mysqli->insert_id;
    }
    // Ensure all help images are in the correct location.
    $studenthelp = rogo_directory::get_directory('help_student');
    $studenthelp->create();
    $studenthelp->copy_from_default();
    // Fix path of help file images as may not be in root web dir.
    InstallUtils::correct_student_path();
    echo "<li>LOADED student_help: " . $ext . "</li>\n";
  }
  $mysqli->commit();

  // Update language packs.
  $update_translationpack = param::optional('update_translationpack', false, param::BOOLEAN, param::FETCH_POST);
  if ($update_translationpack) {
    InstallUtils::download_langpacks();
  }

	/*
   *****   NOW UPDATE THE INSTALLER SCRIPT   *****
   */

  // End of updates -----------------------------------------------------------------

  // Update npm and dependencies.
  try {
    $npm_method = npm_utils::INSTALL_NODEV;
    npm_utils::setup($npm_method);
  } catch (Exception $e) {
      echo "<li class=\"error\">" . $e->getMessage() . "</li>";
  }

  // Final housekeeping activities - put all updates above this line
  $configObject->set_setting('rogo_version', $version, Config::VERSION);
  $updater_utils->execute_query('FLUSH PRIVILEGES', true);
  $updater_utils->execute_query('TRUNCATE sys_errors', true);
  echo "</ol>\n";

  $mysqli->close();
  echo "<div>Ended at " . date("H:i:s") . "</div>";
  echo "\n<h2>" . $string['actionrequired'] . "</h2>\n<ol>";
  echo "\n<li>" . $string['readonly'] . "</li>\n";
  echo "</ol>\n<div>" . $string['finished'] . "</div>\n<div style=\"text-align:center\"><input type=\"button\" class=\"ok\" value=\" " . $string['home'] . " \" onclick=\"go_home()\" /></div><blockquote>\n";
}
?>
<script>
function go_home() {
  window.location='../index.php';
}
</script>
