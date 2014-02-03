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

?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="X-UA-Compatible" content="IE=edge" />
	<meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />

	<title>Rog&#333;: <?php echo $string['statisticsreports'] . ' ' . $configObject->get('cfg_install_type'); ?></title>

	<link rel="stylesheet" type="text/css" href="../css/submenu.css" />
	<link rel="stylesheet" type="text/css" href="../css/header.css" />
	<link rel="stylesheet" type="text/css" href="../css/body.css" />
	<link rel="stylesheet" type="text/css" href="../css/admin.css" />

	<?php echo $configObject->get('cfg_js_root'); ?>
	<script src="../js/staff_help.js" type="text/javascript"></script>
	<script language="JavaScript" src="../js/jquery-1.6.1.min.js"></script>
	<script language="JavaScript" src="../js/sidebar.js"></script>
	<script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>

<body>

<?php
  require '../include/admin_options.inc';
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();

  $mysqli->close();
?>

<div id="content" class="content" style="font-size:80%">
<table class="header">
<tr>
	<th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../admin/index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['statisticsreports']; ?></div></th>
	<th style="text-align:right; vertical-align:top"><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon"></th>
</tr>
</table>

<?php
  $summative_year =  date('Y');
  if (date('n') < 7) {
    $summative_year--;
  }

	$menudata = array();
	$menudata['papersbyschool']			= array('papers_by_school.php?calyear=' . $summative_year, 'formative.png');
	$menudata['questionsbyschool']	= array('questions_by_school.php', 'question_stats.png');
	$menudata['summativeexamstats']	= array('summative_stats.php?calyear=' . $summative_year, 'summative_scheduling.png');
	$menudata['summativefeedback']	= array('summative_feedback.php?calyear=' . $summative_year, 'summative_feedback_stats.png');

	foreach($menudata as $menukey => $menuitem) {
		$parts = explode('.php', $menuitem[0]);
		echo '<a class="blacklink" href="' . $menuitem[0] . '" id="' . $parts[0] . '">';
		echo '<div class="container"><img src="../artwork/' . $menuitem[1] . '" alt="" class="icon" /><br />' . $string[$menukey] . '</div></a>';
	}

?>
</div>

</body>
</html>
