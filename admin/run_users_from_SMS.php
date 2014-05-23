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
* Script to obtain module enrolements from Student Management System (SMS).
*
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

set_time_limit(0);
error_reporting(E_ALL);

require '../include/sysadmin_auth.inc';
require_once '../classes/dateutils.class.php';
require_once '../classes/userutils.class.php';
require_once '../classes/smsutils.class.php';

if ($configObject->get('cfg_sms_api') == '') {
  log_error(0, 'CRON JOB', 'Application Error', "'cfg_sms_api' setting in config.inc.php is set to blank.", 'users_from_SMS.php', 0, '', null, null, null);
  exit();
}
$sms_connection = SmsUtils::GetSmsUtils();

// Calculate what the current academic session is.
$session = (isset($_GET['session']) and $_GET['session'] != '') ? $_GET['session'] : date_utils::get_current_academic_year();
$session_parts = explode('/', $session);

// Do not include deleted modules or non-active modules.
$module_data = $mysqli->prepare("SELECT modules.id, moduleid, sms FROM modules WHERE sms != '' AND mod_deleted IS NULL AND active = 1 ORDER BY moduleid");
$module_data->execute();
$module_data->store_result();
$module_data->bind_result($idMod, $module, $sms);
while ($module_data->fetch()) {
  $sms_connection->update_module_enrolement($module, $idMod, $sms, $mysqli, $session);  
}
$module_data->close();

$errorinfo = $sms_connection->geterrors();

if (count($errorinfo['usernamematch']) > 0) {
  log_error(0, 'CRON JOB', 'Application Warning', implode('\r\n', $errorinfo['usernamematch']), 'users_from_SMS.php', 0, '', null, $errorinfo['usernamematchdata'], null);
}

if (count($errorinfo['unabletodetermineusername']) > 0) {
  log_error(0, 'CRON JOB', 'Application Warning', implode('\r\n', $errorinfo['unabletodetermineusername']), 'users_from_SMS.php', 0, '', null, $errorinfo['unabletodetermineusernamedata'], null);
}

$errorstr = '';
if (count($errorinfo['moduleerrorstate']) > 0) {
  foreach ($errorinfo['moduleerrorstate'] as $key => $value) {
    $cnt= count($value);
    $errorstr .= 'Error state: ' . $key . " <br />\r\n$cnt module(s):: ";
    foreach ($value as $value2) {
      $errorstr .= $value2 . ", ";
    }
    $errorstr .= "<br />\r\n";
  }
  log_error(0, 'CRON JOB', 'Application Warning', $errorstr, 'users_from_SMS.php', 0, '', null, $errorinfo['moduleerrorstatedata'], null);
}

$errorstr = '';
if (count($errorinfo['modulenodata']) > 0) {
  $errorstr .=  "The following " .count($errorinfo['modulenodata']) . " modules returned no data: <br />\r\n";
  foreach ($errorinfo['modulenodata'] as $key => $value) {
    $errorstr .= "$value, ";
  }
  log_error(0, 'CRON JOB', 'Application Warning', $errorstr, 'users_from_SMS.php', 0, '', null, $errorinfo['modulenodatadata'], null);
}

$mysqli->close();
?>
<html>
  <head>
    <title>Rogo</title>
  </head>
  <body>
    Complete.
  </body>
</html>