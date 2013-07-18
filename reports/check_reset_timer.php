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
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.inc';
require_once '../classes/logmetadata.class.php';
require_once '../classes/userutils.class.php';

$metadataID = check_var('metadataID', 'GET', true, false, true);
$userID     = check_var('userID', 'GET', true, false, true);
$paperID    = check_var('paperID', 'GET', true, false, true);

$log_metadata = new LogMetadata($userID, $paperID, $mysqli);

if ($log_metadata->get_record($metadataID) === false) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}
$dateObj = $log_metadata->get_start_datetime();

$user_details = UserUtils::get_user_details($userID, $mysqli);
if ($user_details === false) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$name = $user_details['title'] . ' ' . $user_details['surname'] . ', ' . $user_details['first_names'] . ' (' . $user_details['student_id'] . ')';

$display_format = $configObject->get('cfg_long_date_php') . ' ' . $configObject->get('cfg_short_time_php');

$started = $dateObj->format($display_format);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title><?php echo $string['resettimer']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/check_delete.css" />
</head>

<body>
<table>
<tr>
<td class="icon"><img src="../artwork/reset_timer_48.png" width="48" height="48" alt="<?php echo $string['resettimer']; ?>" /></td>

<td><p><?php echo $string['msg1']; ?></p>
<p><?php echo $name . ' - ' . $started; ?></p>
<br />

<div style="text-align:right">
<form action="do_reset_timer.php" method="post">
<input type="hidden" name="metadataID" value="<?php echo $metadataID; ?>" />
<input type="hidden" name="userID" value="<?php echo $userID; ?>" />
<input type="hidden" name="paperID" value="<?php echo $paperID; ?>" />
<input style="width:140px" type="submit" name="submit" value="<?php echo $string['reset']; ?>" />&nbsp;
<input style="width:80px" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="javascript:window.close();" />
</form>
</div>
</td></tr>
</table>

</body>
</html>
<?php
$mysqli->close();
?>