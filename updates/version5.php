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

require_once '../include/sysadmin_auth.inc';

$language = LangUtils::getLang($cfg_web_root);
if (!isset($_POST['update'])) {
  // If supported lang pack not installed install them.
  if(!LangUtils::langPackInstalled($language)) {
      InstallUtils::download_langpacks();
  }
}

require_once dirname(__DIR__) . '/lang/' . $language . '/install/index.php';
require_once dirname(__DIR__) . '/lang/' . $language . '/updates/version5.php';

// Get the code version.
$version = $configObject->getxml('version');
$migration_path = 'version5';

set_time_limit(0);

// Get the installed version.
$old_version = $configObject->get_setting('core', 'rogo_version');

$updater_utils = new UpdaterUtils($mysqli, $configObject->get('cfg_db_database'));

$render = new render($configObject);
$headerdata = array(
  'css' => array(
    '/css/rogo_logo.css',
    '/css/header.css',
    '/css/updater.css',
  ),
  'scripts' => array(
    '/js/jquery-1.11.1.min.js',
    '/js/jquery.validate.min.js',
    '/js/update.min.js',
  ),
);

$lang['title'] = sprintf($string['updtitle'], $old_version, $version);
$render->render($headerdata, $lang, 'header.html');
$lang['logo_small_txt'] = sprintf($string['logo_small_txt'], $old_version, $version);
$lang['icon'] = $string['icon'];
$data = array();
$data['updating'] = false;
$data['configwarning'] = false;
$data['dberror'] = false;
$data['versionerror'] = false;
$data['staffhelperror'] = false;
$data['stuhelperror'] = false;
$data['langerror'] = false;
if ($updater_utils->check_version("6.4.0")) {
  $data['versionerror'] = true;
  $lang['warning1'] = sprintf($string['warning1'], $old_version);
  $lang['warning2'] = $string['warning2'];
  $render->render($data, $lang, '/updates/update.html');
  $render->render_admin_footer();
  exit;
}
if (!isset($_POST['update'])) {
  $updating = true;
  InstallUtils::checkSoftware();
  $lang['updatefromversion'] = $string['updatefromversion'];
  if (!InstallUtils::configFileIsWriteable()) {
    $lang['warningmsg1'] = $string['warning1'];
    $lang['warningmsg2'] = $string['warning2'];
    $configwarning = true;
  } elseif (!InstallUtils::configPathIsWriteable()) {
    $lang['warningmsg1'] = $string['warning3'];
    $lang['warningmsg2'] = $string['warning4'];
    $configwarning = true;
  } else {
    $lang['menumsg'] = sprintf($string['msg1'], $version);
    $lang['dbusermsg'] = $string['databaseadminuser'];
    $lang['dbmsg'] = $string['msg2'];
    $lang['dbuser'] = $string['dbusername'];
    $lang['dbpass'] = $string['dbpassword'];
    $lang['helpmsg'] = $string['onlinehelpsystems'];
    $lang['updatestaff'] = $string['updatestaffhelp'];
    $lang['updatestudent'] = $string['updatestudenthelp'];
    $lang['transmsg'] = $string['translationpacks'];
    $lang['updatetrans'] = $string['updatetranslationpack'];
    $lang['startupdate'] = $string['startupdate'];
    $data['action'] = url::fromGlobals();
  }
  $data['configwarning'] = $configwarning;
  $data['updating'] = true;
  $render->render($data, $lang, '/updates/update.html');
  $render->render_admin_footer();
} else {

  if ($configObject->get('cfg_db_charset') == null) {
    $cfg_db_charset = 'latin1';
  } else {
    $cfg_db_charset = $configObject->get('cfg_db_charset');
  }

  $mysql_admin_user = param::required('mysql_admin_user', param::TEXT, param::FETCH_POST);
  $mysql_admin_pass = param::required('mysql_admin_pass', param::TEXT, param::FETCH_POST);
  $update_mysqli = DBUtils::get_mysqli_link($configObject->get('cfg_db_host'), $mysql_admin_user, $mysql_admin_pass, $configObject->get('cfg_db_database'), $cfg_db_charset, $notice, $configObject->get('dbclass'), $configObject->get('cfg_db_port'));

  if ($update_mysqli->connect_error) {
    $data['dberror'] = true;
    $lang['dberror'] = sprintf($string['dberror'], $mysql_admin_user);
    $render->render($data, $lang, '/updates/update.html');
    $render->render_admin_footer();
    exit;
  }

  // Set db object in config.
  $configObject->set_db_object($update_mysqli);

  $updater_utils = new UpdaterUtils($update_mysqli, $configObject->get('cfg_db_database'));

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

  $lang['startingupdate'] = $string['startingupdate'];
  $lang['startingat'] = sprintf($string['startingat'], date("H:i:s"));

  $update_mysqli->autocommit(false);

  /*
   *****   ALL UPDATES SHOULD NOW BE PLACED IN DATESTAMPED FILES IN THE version5 FOLDER   *****
   *
   */

  // Run individual update files
  $files = scandir($migration_path);
  foreach ($files as $file) {
    if (StringUtils::ends_with($file, '.php')) {
      include $migration_path . '/' . $file;
      $update_mysqli->commit();
    }
  }

  $update_mysqli->commit();
  
  // 01/05/2013 - Update the online help files.
  $update_staff_help = param::optional('update_staff_help', false, param::BOOLEAN, param::FETCH_POST);
  if ($update_staff_help) {
    $updater_utils->execute_query("TRUNCATE staff_help", false);

    $file = file_get_contents('../install/staff_help.sql');
    $update_mysqli->multi_query($file);
    if ($update_mysqli->error) {
      $lang['staffhelperrormsg'] = $string['showerror'];
      $data['staffhelperror'] = true;
    }
    $ext = '';
    while ($update_mysqli->more_results()) {
      $update_mysqli->next_result();
      if ($update_mysqli->insert_id > 0) $ext = $ext . ' ' . $update_mysqli->insert_id;
    }
    // Ensure all help images are in the correct location.
    $staffhelp = rogo_directory::get_directory('help_staff');
    $staffhelp->create();
    $staffhelp->copy_from_default();
    // Fix path of help file images as may not be in root web dir.
    InstallUtils::correct_staff_path();
    $lang['staffloaded'] = sprintf($string['staffloaded'], $ext);
  }

  $update_student_help = param::optional('update_student_help', false, param::BOOLEAN, param::FETCH_POST);
  if ($update_student_help) {
    $updater_utils->execute_query("TRUNCATE student_help", false);

    $file = file_get_contents('../install/student_help.sql');
    $update_mysqli->multi_query($file);
    if ($update_mysqli->error) {
      $lang['stuhelperrormsg'] = $string['showerror'];
      $data['stuhelperror'] = true;
    }
    $ext = '';
    while ($update_mysqli->more_results()) {
      $update_mysqli->next_result();
      if ($update_mysqli->insert_id > 0) $ext = $ext . ' ' . $update_mysqli->insert_id;
    }
    // Ensure all help images are in the correct location.
    $studenthelp = rogo_directory::get_directory('help_student');
    $studenthelp->create();
    $studenthelp->copy_from_default();
    // Fix path of help file images as may not be in root web dir.
    InstallUtils::correct_student_path();
    $lang['stuloaded'] = sprintf($string['studentloaded'], $ext);
  }
  $update_mysqli->commit();

  // Update language packs.
  $update_translationpack = param::optional('update_translationpack', false, param::BOOLEAN, param::FETCH_POST);
  if ($update_translationpack) {
    try {
      InstallUtils::download_langpacks();
      $lang['langsuccess'] = $string['langsuccess'];
    } catch (Exception $e) {
      $data['langerror'] = true;
      switch ($e->getMessage()) {
        case 'CANNOT_DOWNLOAD_XML':
          $lang['langerror'] = $string['cannotdownloadxml'];
        case 'CANNOT_DOWNLOAD_ZIP':
          $lang['langerror'] = $string['cannotdownloadzip'];
          break;
        default:
          $lang['langerror'] = $string['cannotextract'];
          break;
      }
    }
  }

	/*
   *****   NOW UPDATE THE INSTALLER SCRIPT   *****
   */

  // End of updates -----------------------------------------------------------------

  // Update npm and dependencies.
  try {
    $npm_method = npm_utils::INSTALL_NODEV;
    ob_start();
    npm_utils::setup($npm_method);
    ob_end_clean();
  } catch (Exception $e) {
    $lang['error'] = $e->getMessage();
  }

  // Final housekeeping activities - put all updates above this line
  $configObject->set_setting('rogo_version', $version, Config::VERSION);
  $updater_utils->execute_query('FLUSH PRIVILEGES', false);
  $updater_utils->execute_query('TRUNCATE sys_errors', false);
  $lang['actionrequired'] = $string['actionrequired'];
  $lang['readonly'] = $string['readonly'];
  $lang['finished'] = $string['finished'];
  $lang['home'] = $string['home'];
  $lang['ended'] = sprintf($string['ended'], date("H:i:s"));
  $render->render($data, $lang, '/updates/update.html');
  $render->render_admin_footer();
  $update_mysqli->close();
}