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
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/invigilator_auth.inc';
require_once '../classes/userutils.class.php';
require_once '../classes/noteutils.class.php';

if (isset($_POST['submit'])) {
	if ($_POST['note_id'] == '' or $_POST['note_id'] == '0') {
	  $note_msg = trim($_POST['note']);
		if ($note_msg != '') {  // Check we are not saving nothing.
			StudentNotes::add_note($_POST['student_userID'], $note_msg, $_POST['paperID'], $userObject->get_user_ID(), $mysqli);
		}
	} else {
		StudentNotes::update_note($_POST['note'], $_POST['note_id'], $mysqli);
	}

?>
<!DOCTYPE html>
  <html>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <head><title><?php echo $string['note']; ?></title>
  <script language="JavaScript">
    function closeWindow() {
      window.opener.location.reload(true);
      window.close();
    }
  </script></head>
  <body onload="closeWindow();">
  <form>
    <br />&nbsp;<div align="center"><input type="button" name="home" value="<?php echo $string['ok']; ?>" onclick="closeWindow();" /></div>
  </form>
  <?php
  } else {
    $student_details = UserUtils::get_user_details($_GET['userID'], $mysqli);
		    
		$note_details = StudentNotes::get_note($_GET['paperID'], $_GET['userID'], $mysqli);
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

	<title><?php echo $string['note']; ?></title>

	<link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/notes.css" />
  
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script language="JavaScript">
    $(document).ready(function() {
	    var noteHeight = $(document).height() - 110;
	    $("#note").css('height', noteHeight + 'px')
      $("#note").focus();
    });
	 
	  $(window).resize(function() {
	    var noteHeight = $(document).height() - 110;
	    $("#note").css('height', noteHeight + 'px')
	  });
	</script>
</head>

<body>

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" name="myform">
<table cellpadding="0" cellspacing="0" border="0" style="width:100%">
<tr>
<?php
  if (file_exists($cfg_web_root . 'users/photos/' . $student_details['username'] . '.jpg')) {
    echo "<td style=\"width:180px; text-align:left; vertical-align:bottom\">&nbsp;<strong>" . $student_details['title'] . " " . $student_details['surname'] . "</strong><br />&nbsp;" . $student_details['first_names'] . "<br />&nbsp;" . $student_details['student_id'] . "<br /><img src=\"../users/photos/" . $student_details['username'] . ".jpg\" width=\"180\" height=\"270\" alt=\"Photo\" /></td><td>";
  } else {
    echo '<td><strong>' . $string['studentname'] . ':</strong> ' . $student_details['title'] . ' ' . $student_details['surname'] . ', ' . $student_details['first_names'];
    if ($student_details['student_id'] != '') echo ' (' . $student_details['student_id'] . ')';
    echo '<br />';
  }

  echo "<input type=\"hidden\" name=\"paperID\" value=\"" . $_GET['paperID'] . "\" />\n";
  echo "<strong>" . $string['note'] . ":</strong><br />\n";
  echo "<textarea name=\"note\" id=\"note\" cols=\"60\" rows=\"17\" style=\"font-size:110%; width:100%\" required>" . $note_details['note'] . "</textarea><br />\n";
?>
</td>
</table>
<br />
<div style="text-align:center"><input type="submit" class="ok" name="submit" value="<?php echo $string['save']; ?>" /><input class="cancel" type="button" name="cancel" value="<?php echo $string['cancel']; ?>" onclick="javascript:window.close();" /></div>
<input type="hidden" name="student_userID" value="<?php echo $_GET['userID']; ?>" />
<input type="hidden" name="note_id" value="<?php echo $note_details['note_id']; ?>" />
</form>

</body>
</html>
<?php
}
$mysqli->close();
?>