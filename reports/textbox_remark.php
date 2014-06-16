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

require '../include/staff_auth.inc';
require '../include/errors.inc';
require_once '../classes/paperproperties.class.php';
require_once '../classes/mathsutils.class.php';

$paperID    = check_var('paperID', 'GET', true, false, true);

$module = (isset($_GET['module']) and $_GET['module'] != '') ? $_GET['module'] : '';

if (isset($_POST['submit'])) {
  // Delete any previous remark records
  $result = $mysqli->prepare("DELETE FROM textbox_remark WHERE paperID = ?");
  $result->bind_param('i', $_POST['paperID']);
  $result->execute();
  $result->close();

  for ($student=1; $student<$_POST['student_no']; $student++) {
    if (isset($_POST["student$student"]) and $_POST["student$student"] != '') {
      $result = $mysqli->prepare("INSERT INTO textbox_remark VALUES (NULL, ?, ?)");
      $result->bind_param('ii', $_POST['paperID'], $_POST["student$student"]);
      $result->execute();
      $result->close();
    }
  }
  header("location: ../paper/details.php?paperID=" . $_GET['paperID'] . "&module=" . $module . "&folder=" . $_GET['folder']);
	exit();
} elseif (isset($_POST['submit']) and $_POST['submit'] == 'Cancel') {
  header("location: ../paper/details.php?paperID=" . $_GET['paperID'] . "&module=" . $module . "&folder=" . $_GET['folder']);
	exit();
} else {
	$startdate  = check_var('startdate', 'GET', true, false, true);
	$enddate    = check_var('enddate', 'GET', true, false, true);
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  <title>Rog&#333;: <?php echo $string['secondmark']; ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <style type="text/css">
    body {font-size:90%}
    .pad {padding-left:40px; width:20px}
  </style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>

<body>
<?php
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();

  // Get some paper properties
	$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli, $string);
	
	$paper_total = $properties->get_total_mark();
	$pass_mark = $properties->get_pass_mark();
	$paper_type = $properties->get_paper_type();
	
  echo "<form action=\"" . $_SERVER['PHP_SELF'] . "?paperID=" . $_GET['paperID'] . "&module=" . $module . "&folder=" . $_GET['folder'] . "\" method=\"post\">\n";
  echo "<table class=\"header\" style=\"font-size:90%\">\n<tr><th colspan=\"5\">";
  echo '<div class="breadcrumb"><a href="../index.php">' . $string['home'] . '</a>';
  if (isset($_GET['folder']) and trim($_GET['folder']) != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
  } elseif (isset($_GET['module']) and $_GET['module'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
  }
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $properties->get_paper_title() . '</a></div><div style="margin-left:10px; font-size:220%; color:black; font-weight:bold">' . $string['secondmarkselection'] . '</div></th><th style="width:50%; text-align:right; vertical-align:top"><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon"></th></tr>';

	if ($paper_type == '2') {
		$time_int = 2;
	} else {
		$time_int = 0;
	}
	
	// Get any previous remark settings.
	$remark_array = array();
	$result = $mysqli->prepare("SELECT userID FROM textbox_remark WHERE paperID = ?");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($userID);
  while ($result->fetch()) {
	  $remark_array[$userID] = true;
  }
	$result->close();
	
	if (count($remark_array) > 0) {
		$prev_remark = true;
	} else {
		$prev_remark = false;
	}
	
	// Get back total marks for the paper excluding all textboxes.
	$marks_array = array();
  if ($paper_type == '0') {
		$sql = <<< SQL
			SELECT SUM(adjmark) AS adjmark_total, userID, username
				FROM log0, log_metadata, questions, users
				WHERE log0.metadataID = log_metadata.id
				AND paperID = ?
				AND log0.q_id = questions.q_id
				AND q_type NOT IN ('textbox','info')
				AND log_metadata.userID = users.id
				AND (roles = 'Student' OR roles = 'Graduate')
				AND DATE_ADD(started, INTERVAL $time_int MINUTE) >= ?
				AND started <= ?
			UNION ALL
			SELECT SUM(adjmark) AS adjmark_total, userID, username
				FROM log1, log_metadata, questions, users
				WHERE log1.metadataID = log_metadata.id
				AND paperID = ?
				AND log1.q_id = questions.q_id
				AND q_type NOT IN ('textbox','info')
				AND log_metadata.userID = users.id
				AND (roles = 'Student' OR roles = 'Graduate')
				AND DATE_ADD(started, INTERVAL $time_int MINUTE) >= ?
				AND started <= ?
			GROUP BY metadataID
SQL;
		$result = $mysqli->prepare($sql);
		$result->bind_param('ississ', $_GET['paperID'], $startdate, $enddate, $_GET['paperID'], $startdate, $enddate);
	} else {
		$sql = <<< SQL
			SELECT SUM(adjmark) AS adjmark_total, userID, username
				FROM log$paper_type, log_metadata, questions, users
				WHERE log$paper_type.metadataID = log_metadata.id
				AND paperID = ?
				AND log$paper_type.q_id = questions.q_id
				AND q_type NOT IN ('textbox','info')
				AND log_metadata.userID = users.id
				AND (roles = 'Student' OR roles = 'Graduate')
				AND DATE_ADD(started, INTERVAL $time_int MINUTE) >= ?
				AND started <= ?
				GROUP BY metadataID
SQL;
		$result = $mysqli->prepare($sql);
		$result->bind_param('iss', $_GET['paperID'], $startdate, $enddate);
	}
  $result->execute();
  $result->bind_result($adjmark_total, $userID, $username);
  while ($result->fetch()) {
	  if ($userID != '') {
			$marks_array[$userID]['total'] = $adjmark_total;
			$marks_array[$userID]['userID'] = $userID;
			$marks_array[$userID]['username'] = $username;
			$marks_array[$userID]['student_id'] = '';
		}
  }
	$result->close();

	// Add in total marks for the textbox questions (primary mark).
	$result = $mysqli->prepare("SELECT SUM(mark) AS sum_mark, users.username, users.id, student_id FROM textbox_marking, users LEFT JOIN sid ON users.id = sid.userID WHERE users.id = textbox_marking.student_userID AND paperID = ? AND phase = 1 GROUP BY student_userID ORDER BY student_id");
  $result->bind_param('i', $_GET['paperID']);
  $result->execute();
  $result->bind_result($sum_mark, $username, $userID, $student_id);
	while ($result->fetch()) {
	  if (isset($marks_array[$userID]['total'])) {
			$marks_array[$userID]['total'] += $sum_mark;
	  } else {
			$marks_array[$userID]['total'] = $sum_mark;
		}
		$marks_array[$userID]['userID'] = $userID;
	  $marks_array[$userID]['username'] = $username;
	  $marks_array[$userID]['student_id'] = $student_id;
	}
	$result->close();

	$percent_decimals = $configObject->get('percent_decimals');
		
  $student_no = 1;
	foreach ($marks_array as $userID=>$user_data) {
    $student_id = ($user_data['student_id'] == '') ? '&lt;student ID unknown&gt;' : $user_data['student_id'];
		$username = $user_data['username'];
		$total_mark = $user_data['total'];
		$recordID = $user_data['userID'];
	
		$checked = '';
		if ($prev_remark) {
		  if (isset($remark_array[$recordID])) $checked = ' checked';
		} else {
			if (round(($total_mark/$paper_total)*100) < $pass_mark) $checked = ' checked';
		}
		
    if (round(($total_mark/$paper_total)*100) < $pass_mark) {
      echo "<tr style=\"color:red\"><td class=\"pad\"><input type=\"checkbox\" name=\"student$student_no\" value=\"$recordID\"$checked /></td><td>$username</td><td>$student_id</td><td style=\"text-align:right\">$total_mark</td><td class=\"pad\">" . MathsUtils::formatNumber(($total_mark/$paper_total)*100, $percent_decimals) . "%</td><td>&nbsp;</td></tr>\n";
    } else {
      echo "<tr><td class=\"pad\"><input type=\"checkbox\" name=\"student$student_no\" value=\"$recordID\"$checked /></td><td>$username</td><td>$student_id</td><td style=\"text-align:right\">$total_mark</td><td class=\"pad\">" . MathsUtils::formatNumber(($total_mark/$paper_total)*100, $percent_decimals) . "%</td><td>&nbsp;</td></tr>\n";
    }
    $student_no++;
  }
?>

<tr><td colspan="5">&nbsp;</td></tr>
<tr><td colspan="4" style="text-align:center">
<input type="hidden" name="student_no" value="<?php echo $student_no; ?>" />
<input type="hidden" name="paperID" value="<?php echo $_GET['paperID']; ?>" />
<input type="submit" name="submit" value="<?php echo $string['secondmark']; ?>" style="width:120px" />&nbsp;<input type="submit" name="submit" value="<?php echo $string['cancel']; ?>" style="width:120px" />
</td><td>&nbsp;</td></tr>
</table>
<br />

</form>
</body>
</html>

<?php
}
?>