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


if (isset($_POST['submit'])) {
  $pass_mark = str_replace('%', '', $_POST['xs_pass']);
  $distinction_score = str_replace('%', '', $_POST['xs_distinction']);
  $userID = $userObject->get_user_ID();

  if (isset($_POST['insertID'])) {
    $result = $mysqli->prepare("UPDATE std_set SET pass_score = ?, distinction_score = ? WHERE id = ?");
    $result->bind_param('ddi', $pass_mark, $distinction_score, $_POST['insertID']);
    $result->execute();
    $result->close();

    $insertID = $_POST['insertID'];

    $result = $mysqli->prepare("UPDATE hofstee SET whole_numbers = ?, x1_pass = ?, x2_pass = ?, y1_pass = ?, y2_pass = ?, x1_distinction = ?, x2_distinction = ?, y1_distinction = ?, y2_distinction = ? WHERE std_setID = ?");
    $result->bind_param('iddddddddi', $_POST['whole_numbers'], $_POST['x1_pass'], $_POST['x2_pass'], $_POST['y1_pass'], $_POST['y2_pass'], $_POST['x1_distinction'], $_POST['x2_distinction'], $_POST['y1_distinction'], $_POST['y2_distinction'], $insertID);
    $result->execute();
    $result->close();
  } else {
    $result = $mysqli->prepare("INSERT INTO std_set VALUES (NULL, ?, ?, NOW(), 'Hofstee', 'No', ?, ?)");
    $result->bind_param('iidd', $userID, $paperID, $pass_mark, $distinction_score);
    $result->execute();
    $result->close();
    
    $insertID = $mysqli->insert_id;
    
    $result = $mysqli->prepare("INSERT INTO hofstee VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $result->bind_param('iidddddddd', $insertID, $_POST['whole_numbers'], $_POST['x1_pass'], $_POST['x2_pass'], $_POST['y1_pass'], $_POST['y2_pass'], $_POST['x1_distinction'], $_POST['x2_distinction'], $_POST['y1_distinction'], $_POST['y2_distinction']);
    $result->execute();
    $result->close();
  }
  
  if (isset($_POST['whole_numbers'])) {
    $checked = ' checked="checked"';
  } else {
    $checked = '';
  }
  $x1_pass = $_POST['x1_pass'];
  $x2_pass = $_POST['x2_pass'];
  $y1_pass = $_POST['y1_pass'];
  $y2_pass = $_POST['y2_pass'];
  $x1_distinction = $_POST['x1_distinction'];
  $x2_distinction = $_POST['x2_distinction'];
  $y1_distinction = $_POST['y1_distinction'];
  $y2_distinction = $_POST['y2_distinction'];
  
} elseif (isset($_GET['std_setID'])) {
  $insertID = $_GET['std_setID'];
  
  $result = $mysqli->prepare("SELECT whole_numbers, x1_pass, x2_pass, y1_pass, y2_pass, x1_distinction, x2_distinction, y1_distinction, y2_distinction FROM hofstee WHERE std_setID = ?");
  $result->bind_param('i', $insertID);
  $result->execute();
  $result->bind_result($whole_numbers, $x1_pass, $x2_pass, $y1_pass, $y2_pass, $x1_distinction, $x2_distinction, $y1_distinction, $y2_distinction);
  $result->fetch();
  $result->close();
  
  if ($whole_numbers == 1) {
    $checked = ' checked="checked"';
  } else {
    $checked = '';
  }
  
} else {
  // Default values no POST and no editing existing review
  $checked = '';
  
	$x1_pass = 10;
  $x2_pass = 90;
  $y1_pass = 10;
  $y2_pass = 90;
  $x1_distinction = 10;
  $x2_distinction = 90;
  $y1_distinction = 10;
  $y2_distinction = 90;
	
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
h1 {margin-left:10px; font-size:140%}
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
	$stats = array_values($results_cache->get_paper_cache($paperID));
  
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
  
  echo "<table style=\"margin:10px\">";
  echo "<tr><td style=\"min-width:150px\">" . $string['cohortsize'] . "</td><td>" . count($marks) . "</td></tr>\n";
  echo "<tr><td>" . $string['maximumscore'] . "</td><td>" . round($stats[1], 1) . "%</td></tr>\n";
  echo "<tr><td>".  $string['topquartile'] . "</td><td>" . round($stats[6], 1) . "%</td></tr>\n";
  echo "<tr><td>".  $string['median'] . "</td><td>" . round($stats[5], 1) . "%</td></tr>\n";
  echo "<tr><td>".  $string['lowerquartile'] . "</td><td>" . round($stats[4], 1) . "%</td></tr>\n";
  echo "<tr><td>".  $string['minimumscore'] . "</td><td>" . round($stats[3], 1) . "%</td></tr>\n";
  echo "</table>\n";

	echo "<div id=\"canvas_div_pass\" style=\"float:left\">\n";
	echo "<h1>Pass Mark</h1>\n";
  echo "<canvas id=\"canvas_graph_pass\" width=\"480\" height=\"450\"></canvas>\n";
	echo "<table style=\"margin-left:auto; margin-right:auto\">\n";
	echo "<tr><td class=\"pass\">". $string['minpass'] . "</td><td class=\"pass\">". $string['maxpass'] . "</td><td class=\"fail\">". $string['minfail'] . "</td><td class=\"fail\">". $string['maxfail'] . "</td><td><strong>". $string['cutscore'] . "</strong></td></tr>\n";
	echo "<tr>\n";
  if (isset($_POST['whole_numbers'])) {
    $checked = ' checked="checked"';
  } else {
    $checked = '';
  }
	echo "<td><input type=\"text\" size=\"5\" name=\"x1_pass\" id=\"x1_pass\" class=\"tf\" value=\"" . $x1_pass . "\" /></td>\n";
	echo "<td><input type=\"text\" size=\"5\" name=\"x2_pass\" id=\"x2_pass\" class=\"tf\" value=\"" . $x2_pass . "\" /></td>\n";
	echo "<td><input type=\"text\" size=\"5\" name=\"y1_pass\" id=\"y1_pass\" class=\"tf\" value=\"" . $y1_pass . "\" /></td>\n";
	echo "<td><input type=\"text\" size=\"5\" name=\"y2_pass\" id=\"y2_pass\" class=\"tf\" value=\"" . $y2_pass . "\" /></td>\n";
	echo "<td><input type=\"text\" size=\"5\" name=\"xs_pass\" id=\"xs_pass\" readonly /><input type=\"hidden\" size=\"5\" name=\"ys_pass\" id=\"ys_pass\" readonly /></td>\n";
	echo "</tr>";
  echo "<tr><td colspan=\"5\"><input type=\"checkbox\" name=\"whole_numbers\" id=\"checkbox\"$checked />" . $string['integeronly'] . "</td></tr>";
	echo "</table>\n</div>\n";
	
	echo "<script type=\"text/javascript\" src=\"../html5/hofstee.js\"></script>\n";
	echo "<script type='text/javascript'>
		var lang_cohort = '".  $string['cohort'] . "';
		var lang_correct = '".  $string['correct'] . "';
		var marks = ".  json_encode($marks) . ";
		var stats = ".  json_encode($stats) . ";
    hofstee_plot('canvas_graph_pass','pass');
		</script>";
  
	echo "<div id=\"canvas_div_distinction\" style=\"float:left\">\n";
	echo "<h1>Distinction Mark</h1>\n";
	echo "<canvas id=\"canvas_graph_distinction\" width=\"480\" height=\"450\"></canvas><br />\n";
	echo "<table style=\"margin-left:auto; margin-right:auto\">\n";
	echo "<tr><td class=\"pass\">". $string['minpass'] . "</td><td class=\"pass\">". $string['maxpass'] . "</td><td class=\"fail\">". $string['minfail'] . "</td><td class=\"fail\">". $string['maxfail'] . "</td><td><strong>". $string['cutscore'] . "</strong></td></tr>\n";
	echo "<tr>\n";
  if (isset($_POST['checkbox'])) {
    $checked = ' checked="checked"';
  } else {
    $checked = '';
  }
	echo "<td><input type=\"text\" size=\"5\" name=\"x1_distinction\" id=\"x1_distinction\" class=\"tf\" value=\"" . $x1_distinction . "\" /></td>\n";
	echo "<td><input type=\"text\" size=\"5\" name=\"x2_distinction\" id=\"x2_distinction\" class=\"tf\" value=\"" . $x2_distinction . "\" /></td>\n";
	echo "<td><input type=\"text\" size=\"5\" name=\"y1_distinction\" id=\"y1_distinction\" class=\"tf\" value=\"" . $y1_distinction . "\" /></td>\n";
	echo "<td><input type=\"text\" size=\"5\" name=\"y2_distinction\" id=\"y2_distinction\" class=\"tf\" value=\"" . $y2_distinction . "\" /></td>\n";
	echo "<td><input type=\"text\" size=\"5\" name=\"xs_distinction\" id=\"xs_distinction\" readonly /><input type=\"hidden\" size=\"5\" name=\"ys_distinction\" id=\"ys_distinction\" readonly /></td>\n";
	echo "</tr>";
  echo "<tr><td colspan=\"5\">&nbsp;</td></tr>\n";
	echo "</table>\n</div>\n";
	
	echo "<script type='text/javascript'>
    hofstee_plot('canvas_graph_distinction','distinction');
		</script>";
  
  if (isset($insertID)) {
    echo "<input type=\"hidden\" name=\"insertID\" value=\"$insertID\" />\n";
  }

?>
<br clear="all" />
<div style="text-align:center"><input type="submit" name="submit" value="<?php echo $string['savemarks']; ?>" /></div>
</form>
</body>
</html>
