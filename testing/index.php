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
* Rogō Test Harness.
* 
* @author Anthony Brown
* @version 1.0
* @copyright Copyright (c) 2014 The University of Nottingham
* @package
*/

require '../include/sysadmin_auth.inc';
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>"/>

	<title>Rog&#333;: Test Suite</title>

	<style>
		.content {font-size:90%}
		aside, figure, footer, header, hgroup, nav, section { display: block; clear: both; }
		article { width:50%; height: 80%; float: left }
		ol {list-style-type:decimal}
		li {padding-left:10px; margin-left:40px !important}
	</style>
	<link rel="stylesheet" type="text/css" href="../css/body.css" />
	<link rel="stylesheet" type="text/css" href="../css/header.css" />
	<link rel="stylesheet" type="text/css" href="../css/screen.css" />
	
  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
</head>
<body>
<?php
  require '../include/toprightmenu.inc';

	echo draw_toprightmenu();
?>
<div id="content" class="content">
	<table class="header">
		<tr><th><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="../admin/index.php"><?php echo $string['administrativetools']; ?></a></div><div style="font-size:220%; font-weight:bold; margin-left:10px">Test Suite</div></th><th style="text-align:right; vertical-align:top; padding-right:6px"><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon"></th></tr>
	</table>
	<br />
	<ol>
		<li><a href="./unittest.php">Unit tests</a></li>
		<li><a href="./selenium/README.txt">Selenium tests</a></li>
		<li><a href="./lang_test.php">Check for missing strings in language files</a></li>
		<li><a href="./class_totals_form.php">Check Class Totals reports between 2 different servers</a></li>
		<li><a href="./class_totals_with_script.php">Check Class Totals against Exam Script (internal constancy)</a></li>
		<li><a href="./database_grants.php">Database grants</a></li>
		<li><a href="./database_indexes.php">Database indexes</a></li>
		<li><a href="./database_structure.php">Database structure</a></li>
	</ol>
</div>
</body>
</html>
