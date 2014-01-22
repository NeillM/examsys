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

	<title>Rog&#333;: Stats</title>

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
  <script language="JavaScript">
    function jumpTo() {
      document.location = 'papers_by_school.php?year=' + $('#year').val();
    }
  </script>
</head>

<body>
<?php
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();
?>
<table class="header" style="font-size:90%">
<tr>
<th colspan="2"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../statistics/index.php"><?php echo $string['statistics']; ?></a></div></th>
<th style="text-align:right; vertical-align:top"><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon"></th>
</tr>
<tr>
<th colspan="2"><div style="margin-left:10px; font-size:200%"><strong><?php echo $string['summativeexamstats']; ?>:</strong> <?php echo $_GET['year']; ?>/<?php echo (substr($_GET['year'],2,2)+1); ?></th>
<th style="text-align:right; vertical-align:bottom; padding-bottom:2px; padding-right:6px"><select name="year" id="year" onchange="jumpTo()">
<?php
for ($i=2005; $i<=date('Y'); $i++) {
  if ($i == $_GET['year']) {
    echo "<option value=\"$i\" selected>$i/" . substr(($i+1),2,2) . "</option>\n";
  } else {
    echo "<option value=\"$i\">$i/" . substr(($i+1),2,2) . "</option>\n";
  }
}
?>
</select></th>
</tr>
</table>

<blockquote>
<table border="0" style="width:100%" class="stats">
<tr>
<th>School</th>
<th>Formative</th>
<th>Progress Test</th>
<th>Summative</th>
<th>Survey</th>
<th>OSCEs</th>
<th>Offline</th>
<th>Peer Review</th>
</tr>
<?php
$master_array = array();

$result = $mysqli->prepare("SELECT id, school FROM schools WHERE school != 'Training' ORDER BY school");
$result->execute();
$result->bind_result($id, $school);
while ($result->fetch()) {
  $master_array[$school]['id'] = $id;
	$master_array[$school]['paper_types'] = array(0, 0, 0, 0, 0, 0, 0);
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
		if ($_GET['year']) {
		  $year = $_GET['year'];
		
			$date_range .= " AND ((start_date > {$year}0901000000 AND end_date <= " . ($year + 1) . "0831235959)";  // Start and end within year
			
			$date_range .= " OR (start_date <= {$year}0901000000 AND end_date >= " . ($year + 1) . "0831235959)";   // Paper continuing this year
			
			$date_range .= " OR (start_date <= {$year}0901000000 AND end_date >= {$year}0901000000 AND end_date <= " . ($year + 1) . "0831235959)";   // End date within year
			
			$date_range .= " OR (start_date > {$year}0901000000 AND start_date <= " . ($year + 1) . "0831235959 AND end_date >= " . ($year + 1) . "0831235959))";   // Start date within year
		}
		
		$result = $mysqli->prepare("SELECT DISTINCT properties.property_id, paper_title, paper_type FROM properties, properties_modules WHERE properties.property_id = properties_modules.property_id $date_range AND idMod IN (" . implode(',', $moduleIDs) . ") GROUP BY property_id");
		$result->execute();
		$result->bind_result($paperID, $paper_title, $paper_type);
		while ($result->fetch()) {
			$master_array[$school]['paper_types'][intval($paper_type)]++;
		}
		$result->close();
	}
}

foreach ($master_array as $school => $data) {
  echo "<tr><td>" . $school . "</td>";
	
	for ($i=0; $i<=6; $i++) {
	  if ($data['paper_types'][$i] == 0) {
			echo "<td class=\"n grey\">" . $data['paper_types'][$i] . "</td>";
		} else {
			echo "<td class=\"n\">" . $data['paper_types'][$i] . "</td>";
		}
	}
	echo "</tr>\n";
}
?>
</table>
</blockquote>

</body>
</html>