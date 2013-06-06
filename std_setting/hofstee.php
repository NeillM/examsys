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
* Hofstee plot
*
* @author Nikodem Miranowicz
* @version 1.0
* @copyright Copyright (c) 2013 The University of Nottingham
* @package
*/

require '../include/staff_auth.inc';
require_once '../include/errors.inc';
require_once '../classes/results_cache.class.php';
require_once '../classes/paperproperties.class.php';

$paperID = check_var('paperID', 'GET', true, false, true);
$properties = PaperProperties::get_paper_properties_by_id($paperID, $mysqli);
if (!$properties) {
  $msg = sprintf($string['furtherassistance'], $configObject->get('support_email'), $configObject->get('support_email'));
  $notice->display_notice_and_exit($mysqli, $string['pagenotfound'], $msg, $string['pagenotfound'], '../artwork/page_not_found.png', '#C00000', true, true);
}

$pass_mark = NULL;
$distinction_mark = NULL;

if (isset($_POST['passmark'])) {
  $pass_mark = floor($_POST['xs']);

  if (isset($_POST['insertID'])) {
    $result = $mysqli->prepare("UPDATE hofstee SET pass_score = ? WHERE id = ?");
    $result->bind_param('ii', $pass_mark, $_POST['insertID']);
    $result->execute();
    $result->close();
  }
} elseif (isset($_POST['distinction'])) {
  $distinction = floor($_POST['xs']);
  
  if (isset($_POST['insertID'])) {
    $result = $mysqli->prepare("UPDATE hofstee SET distinction = ? WHERE id = ?");
    $result->bind_param('ii', $distinction, $_POST['insertID']);
    $result->execute();
    $result->close();
  }
}

if ((isset($_POST['passmark']) or isset($_POST['distinction'])) and !isset($_POST['insertID'])) {
  $userID = $userObject->get_user_ID();
  
  $result = $mysqli->prepare("INSERT INTO hofstee VALUES (NULL, ?, ?, NOW(), ?, ?)");
  $result->bind_param('iiii', $userID, $paperID, $pass_mark, $distinction_mark);
  $result->execute();
  $result->close();
  
  $insertID = $mysqli->insert_id;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

<title><?php echo $string['hofstee'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

<link rel="stylesheet" type="text/css" href="../css/body.css" />
<link rel="stylesheet" type="text/css" href="../css/header.css" />
<link rel="stylesheet" type="text/css" href="../css/warnings.css" />
<style type="text/css">
body {font-size:85%}
.pass {color:#76923C}
.fail {color:#C00000}
</style>

<script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
<script type="text/javascript" src="../js/staff_help.js"></script>

</head>
<body>
<form action="<?php echo $_SERVER['PHP_SELF'] . '?paperID=' . $paperID; ?>" method="post">
<?php
	$results_cache = new ResultsCache($mysqli);
	$marks = array_values($results_cache->get_paper_marks_by_paper($paperID, true));
	
  echo "<table class=\"header\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">\n";
  echo "<tr><th class=\"h\">";
  echo '<div class="breadcrumb"><a href="../staff/index.php">' . $string['home'] . '</a>';
  if (isset($_GET['folder']) and $_GET['folder'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?folder=' . $_GET['folder'] . '">' . folder_utils::get_folder_name($_GET['folder'], $mysqli) . '</a>';
  } elseif (isset( $_GET['module']) and $_GET['module'] != '') {
    echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../folder/details.php?module=' . $_GET['module'] . '">' . module_utils::get_moduleid_from_id($_GET['module'], $mysqli) . '</a>';
  }
  echo '&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../paper/details.php?paperID=' . $_GET['paperID'] . '">' . $properties->get_paper_title() . '</a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="index.php?paperID=' . $paperID . '&module=&folder=">' . $string['standardssetting'] . '</a></div>';

  echo "<span style=\"margin-left:10px; font-size:200%; color:black\"><strong>" . $string['hofstee'] . "</span></th><th class=\"h\" style=\"text-align:right; vertical-align:top; padding-top:2px; padding-right:6px\"><a href=\"#\" onclick=\"launchHelp(30); return false;\"><img src=\"../artwork/small_help_icon.gif\" width=\"16\" height=\"16\" alt=\"" . $string['help'] . "\" /></a></th></tr>\n";
  echo "<tr><th colspan=\"2\" class=\"bevel\"></th></tr>\n";
  echo "</table>\n";

	echo "<div id=\"canvas_div\">\n";
	echo "<canvas id=\"canvas_graph\" width=\"650\" height=\"600\"></canvas><br>\n";
	echo "<table><tr><td style=\"width:0px\">&nbsp;</td><td>&nbsp;</td>\n";
	echo "<td class=\"pass\">". $string['minpass'] . "</td><td class=\"pass\">". $string['maxpass'] . "</td><td class=\"fail\">". $string['minfail'] . "</td><td class=\"fail\">". $string['maxfail'] . "</td><td>". $string['cutscore'] . "</td><td>". $string['cutpercent'] . "</td>\n";
	echo "</tr><tr><td>&nbsp;</td>\n";
	echo "<td><input type='checkbox' name='checkbox' id='checkbox'>" . $string['integeronly'] . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>\n";
	echo "<td><input type=\"text\" size=\"5\" name=\"x1\" id=\"x1\" class=\"tf\" ";
		if (isset($_POST['x1'])) echo "value=\"" . $_POST['x1'] . "\" ";
		echo "/></td>\n";
	echo "<td><input type=\"text\" size=\"5\" name=\"x2\" id=\"x2\" class=\"tf\" ";
		if (isset($_POST['x2'])) echo "value=\"" . $_POST['x2'] . "\" ";
		echo "/></td>\n";
	echo "<td><input type=\"text\" size=\"5\" name=\"y1\" id=\"y1\" class=\"tf\" ";
		if (isset($_POST['y1'])) echo "value=\"" . $_POST['y1'] . "\" ";
		echo "/></td>\n";
	echo "<td><input type=\"text\" size=\"5\" name=\"y2\" id=\"y2\" class=\"tf\" ";
		if (isset($_POST['y2'])) echo "value=\"" . $_POST['y2'] . "\" ";
		echo "/></td>\n";
	echo "<td><input type=\"text\" size=\"5\" name=\"xs\" id=\"xs\" readonly /></td>\n";
	echo "<td><input type=\"text\" size=\"5\" name=\"ys\" id=\"ys\" readonly /></td>\n";
	echo "</tr><tr>";
	echo "<td></td><td colspan=\"7\" style=\"text-align:center; padding-top:10px\"><input type=\"submit\" name=\"passmark\" value=\"" . $string['passmark'] . "\" style=\"width:150px\" />&nbsp;<input type=\"submit\" name=\"distinction\" value=\"" . $string['distinction'] . "\" style=\"width:150px\" /></td>";
	echo "</tr></table>\n";
	
	echo "<script type='text/javascript'>
		var lang_cohort = '".  $string['cohort'] . "';
		var lang_correct = '".  $string['correct'] . "';			
		var marks = ".  json_encode($marks) . ";
		</script>";
	echo "<script type=\"text/javascript\" src=\"../html5/hofstee.js\"></script></div>\n";
  if (isset($insertID)) {
    echo "<input type=\"hidden\" name=\"insertID\" value=\"$insertID\" />\n";
  }

?>
</form>
</body>
</html>
