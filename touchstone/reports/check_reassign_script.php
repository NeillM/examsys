<?php
// This file is part of TouchStone
//
// TouchStone is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// TouchStone is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with TouchStone.  If not, see <http://www.gnu.org/licenses/>.

/**
* 
* @author Simon Wilkinson
* @version 1.0
* @copyright Copyright (c) 2011 The University of Nottingham
* @package
*/

  require '../include/staff_auth.inc';
?>
<html>
<head>
<title>Reassign Script to User</title>
<style>
body {background-color:#EEEEEE; color:black; font-family:Arial,sans-serif; margin:0px}
</style>
</head>

<body>
<?php
// Check if the exam is still running. Re-assignment mid-exam would upset the data.
$result = $mysqli->prepare("SELECT UNIX_TIMESTAMP(end_date) FROM properties WHERE property_id=?");
$result->bind_param('i', $_GET['paperID']);
$result->execute();
$result->bind_result($end_date);
$result->fetch();
$result->close();

if (time() < $end_date) {
  echo "<p><strong>Warning</strong><p><p>Exam scripts cannot be reassigned mid exam.<br />Please wait until after the exam has finished</p>\n";
  exit;
}


$temp_userID = '';

if (isset($_POST['submit'])) {
  $result = $mysqli->prepare("SELECT userID FROM sid WHERE student_id=?");
  $result->bind_param('s', $_POST['student_id']);
  $result->execute();
  $result->bind_result($temp_userID);
  $result->fetch();
  $result->close();

  // Get details of the temporary user.
  $result = $mysqli->prepare("SELECT username FROM users WHERE id=?");
  $result->bind_param('i', $_POST['temp_userID']);
  $result->execute();
  $result->bind_result($temporay_user_username);
  $result->fetch();
  $result->close();
} else {
  // Get all the details from 'temp_users' for given userID.
  $result = $mysqli->prepare("SELECT temp_users.id, temp_users.title, temp_users.first_names, temp_users.surname, student_id, assigned_account FROM users, temp_users WHERE users.id=? AND users.username=temp_users.assigned_account");
  $result->bind_param('i', $_GET['userID']);
  $result->execute();
  $result->bind_result($temp_account_id, $temp_title, $temp_first_names, $temp_surname, $temp_student_id, $assigned_account);
  $result->fetch();
  $result->close();

  // Look up the temporary information in 'users'.
  if ($temp_student_id != '') {
    // Try student number lookup.
    $result = $mysqli->prepare("SELECT userID FROM sid WHERE student_id=?");
    $result->bind_param('i', $temp_student_id);
    $result->execute();
    $result->bind_result($temp_userID);
    $result->fetch();
    $result->close();
  }
  if ($temp_userID == '') {
    // If no student number try the other details.
    $first_names = trim($temp_first_names) . '%';
	$temp_surname = trim($temp_surname);
	$temp_title = trim($temp_title);
    $result = $mysqli->prepare("SELECT id FROM users WHERE surname=? AND first_names LIKE ? AND title=?");
    $result->bind_param('sss', $temp_surname, $first_names, $temp_title);
    $result->execute();
    $result->bind_result($temp_userID);
    $result->fetch();
    $result->close();
  }

  if ($temp_userID != '') {
    // Get details of the temporary user.
    $result = $mysqli->prepare("SELECT username FROM users WHERE id=?");
    $result->bind_param('i', $temp_userID);
    $result->execute();
    $result->bind_result($temporay_user_username);
    $result->fetch();
    $result->close();
  }
}

if ($temp_userID != '') {
  echo "<form name=\"myform\" method=\"post\" action=\"do_reassign_script.php\">\n";

  // Look up the user you wish to reassign to.
  $result = $mysqli->prepare("SELECT username, title, surname, first_names, email, grade FROM users WHERE id=?");
  $result->bind_param('i', $temp_userID);
  $result->execute();
  $result->bind_result($username, $title, $surname, $first_names, $email, $grade);
  $result->fetch();
  $result->close();

  echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" style=\"width:100%; font-size:100%\">\n";

  $student_photo =  $cfg_web_root . 'touchstone/users/photos/' . $username . '.jpg';
  if (file_exists($student_photo)) {
    echo "<tr style=\"background-color:white\"><td valign=\"top\" rowspan=\"2\" width=\"70\" style=\"background-color:white\"><img src=\"../users/photos/$username.jpg\" width=\"180\" height=\"270\" alt=\"Student Photo\" border=\"0\" /></td>";
  } else {
    echo "<tr style=\"background-color:white\"><td valign=\"top\" rowspan=\"2\" width=\"70\" style=\"background-color:white\"><img src=\"../artwork/user_icon.png\" width=\"58\" height=\"61\" alt=\"User Icon\" border=\"0\" /></td>";
  }
  if ($username == '') {
    echo "<td style=\"background-color:white\">&nbsp;</td><td style=\"color:#C00000; background-color:white; vertical-align:top\"><br /><strong>Warning:</strong> student ID " . trim($_GET['student_id']) .  " not found.<br /><br /></td></tr>";
    echo "<tr><td style=\"background-color:white\" colspan=\"3\">&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"3\" style=\"text-align:center; padding-top:10px\"><input type=\"button\" name=\"back\" value=\"&lt; Back\" style=\"width:100px\" onclick=\"history.back();\" /></td></tr>\n";
  } else {
    echo "<td style=\"background-color:white\">&nbsp;</td><td style=\"background-color:white; vertical-align:top\"><br />";
	if (isset($_POST['student_id'])) echo trim($_POST['student_id']);
	echo "<br /><span style=\"font-size:120%; font-weight:bold\">$title $surname, <span style=\"color:#C0C0C0\">$first_names</span></span><br /><a href=\"mailto:$email\">$email</a></td></tr>";
    echo "<tr><td style=\"background-color:white\">&nbsp;</td><td style=\"background-color:white; vertical-align:bottom; text-align:justify; padding-right:4px; padding-bottom:6px; font-size:90%\">Reassign <strong>$assigned_account</strong> to the student detailed above. Please ensure that these details are correct and this is your intention.</td></tr>\n";
    echo "<tr><td colspan=\"3\">&nbsp;</td></tr>\n";
    echo "<tr><td colspan=\"3\" style=\"text-align:center\"><input type=\"submit\" name=\"ok\" value=\"OK\" style=\"width:100px\" />&nbsp;&nbsp;<input type=\"submit\" name=\"cancel\" value=\"Cancel\" style=\"width:100px\" /></td></tr>\n";
  }
  if (isset($_GET['userID'])) {
    echo "</table>\n<input type=\"hidden\" name=\"log_type\" value=\"" . $_GET['log_type'] . "\" />\n<input type=\"hidden\" name=\"temp_userID\" value=\"" . $_GET['userID'] . "\" />\n<input type=\"hidden\" name=\"userID\" value=\"$temp_userID\" />\n<input type=\"hidden\" name=\"paperID\" value=\"" . $_GET['paperID'] . "\" />\n<input type=\"hidden\" name=\"started\" value=\"" . $_GET['started'] . "\" />\n<input type=\"hidden\" name=\"assigned_account\" value=\"$assigned_account\" />\n<input type=\"hidden\" name=\"grade\" value=\"$grade\" />\n</form>\n";
  } else {
    echo "</table>\n<input type=\"hidden\" name=\"log_type\" value=\"" . $_POST['log_type'] . "\" />\n<input type=\"hidden\" name=\"temp_userID\" value=\"" . $_POST['temp_userID'] . "\" />\n<input type=\"hidden\" name=\"userID\" value=\"$temp_userID\" />\n<input type=\"hidden\" name=\"paperID\" value=\"" . $_POST['paperID'] . "\" />\n<input type=\"hidden\" name=\"started\" value=\"" . $_POST['started'] . "\" />\n<input type=\"hidden\" name=\"assigned_account\" value=\"$assigned_account\" />\n<input type=\"hidden\" name=\"grade\" value=\"$grade\" />\n</form>\n";
  }
} else {
?>

<form name="myform" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">

<div style="margin-top:10px; margin-left:5px">Student ID: 
<input type="text" name="student_id" size="20" />
<input type="submit" name="submit" value="Find" style="width:80px" />
<input type="hidden" name="temp_userID" value="<?php echo $_GET['userID']; ?>" />
<input type="hidden" name="paperID" value="<?php echo $_GET['paperID']; ?>" />
<input type="hidden" name="log_type" value="<?php echo $_GET['log_type']; ?>" />
<input type="hidden" name="started" value="<?php echo $_GET['started']; ?>" />
</div>

</form>
<?php
}
?>

</body>
</html>