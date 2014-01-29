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

require '../include/sysadmin_auth.inc';
require '../include/sidebar_menu.inc';
require '../include/errors.inc';
?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

	<title>Rog&#333;: <?php echo $string['questionsbyschool']  . ' ' . $configObject->get('cfg_install_type'); ?></title>

  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/statistics.css" />
	<style>
	  body {font-size:90%}
		.grey {color:#C0C0C0}
	</style>
	
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>

<body>
<?php
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();
?>
<table class="header" style="font-size:90%">
<tr>
<th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../admin/index.php"><?php echo $string['administrativetools']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../statistics/index.php"><?php echo $string['statistics']; ?></a></div></th>
<th style="text-align:right; vertical-align:top"><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon"></th>
</tr>
<tr>
<th colspan="2"><div style="margin-left:10px; font-size:200%"><strong><?php echo $string['questionsbyschool']; ?></th>
</tr>
</table>

<blockquote>
<table border="0" style="width:100%" class="stats">
<tr>
<th>School</th>
<?php
	$types = array('blank', 'dichotomous', 'flash', 'hotspot', 'labelling', 'likert', 'matrix', 'mcq', 'mrq', 'rank', 'textbox', 'info', 'extmatch', 'random', 'sct', 'keyword_based', 'true_false', 'area', 'enhancedcalc');
  foreach ($types as $type) {
	  echo "<th>$type</th>";
	}
?>
</tr>
<?php
$master_array = array();

$result = $mysqli->prepare("SELECT id, school FROM schools WHERE school != 'Training' ORDER BY school");
$result->execute();
$result->bind_result($id, $school);
while ($result->fetch()) {
  $master_array[$school]['id'] = $id;
	$master_array[$school]['types'] = array('blank'=>0, 'dichotomous'=>0, 'flash'=>0, 'hotspot'=>0, 'labelling'=>0, 'likert'=>0, 'matrix'=>0, 'mcq'=>0, 'mrq'=>0, 'rank'=>0, 'textbox'=>0, 'info'=>0, 'extmatch'=>0, 'random'=>0, 'sct'=>0, 'keyword_based'=>0, 'true_false'=>0, 'area'=>0, 'enhancedcalc'=>0);
}
$result->close();

foreach ($master_array as $school => $data) {
	// Get the modules which belong in the school first.
	$moduleIDs = array();

	$result = $mysqli->prepare("SELECT id FROM modules WHERE schoolid = ?");
	$result->bind_param('i', $data['id']);
	$result->execute();
	$result->bind_result($id);
	while ($result->fetch()) {
		$moduleIDs[] = $id;
	}
	$result->close();
	
	$master_array[$school]['module_no'] = count($moduleIDs);

	if (count($moduleIDs) > 0) {
		// Get the papers.
		$date_range = '';
				
		$result = $mysqli->prepare("SELECT DISTINCT questions.q_id, q_type FROM questions, questions_modules WHERE questions.q_id = questions_modules.q_id AND idMod IN (" . implode(',', $moduleIDs) . ") GROUP BY questions.q_id");
		$result->execute();
		$result->bind_result($q_id, $q_type);
		while ($result->fetch()) {
			$master_array[$school]['types'][$q_type]++;
		}
		$result->close();
	}
}

foreach ($master_array as $school => $data) {
  echo "<tr><td>" . $school . "</td>";
	
	foreach ($types as $type) {
	  if ($data['types'][$type] == 0) {
			echo "<td class=\"n grey\">" . $data['types'][$type] . "</td>";
		} else {
			echo "<td class=\"n\">" . number_format($data['types'][$type]) . "</td>";
		}
	}
	echo "</tr>\n";
}
?>
</table>
</blockquote>

</body>
</html>