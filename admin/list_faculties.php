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
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta http-equiv="content-type" content="text/html;charset=<?php echo $configObject->get('cfg_page_charset') ?>" />
  
  <title>Rog&#333;: <?php echo $string['faculties'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery_tablesorter/jquery.tablesorter.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/list.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script>
    $(document).ready(function() {
      $("#maindata").tablesorter({ 
        sortList: [[0,0]] 
      });

    });
  </script>
</head>

<body>
<?php
  require '../include/faculty_options.inc';
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();
?>
<div id="content">

<div class="head_title">
  <img src="../artwork/toprightmenu.gif" id="toprightmenu_icon">
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools'] ?></a></div>
  <div class="page_title"><?php echo $string['faculties'] ?></div>
</div>
  
<table id="maindata" class="header tablesorter" cellspacing="0" cellpadding="0" border="0" style="width:100%">
<thead>
<tr>
<th class="col10" style="width:25%"><?php echo $string['name']; ?></th><th style="width:25%"><?php echo $string['schoolno']; ?></th><th style="width:50%" class="vert_div"></th>
</tr>
</thead>
<tbody>
<?php
$old_faculty = '';
$id = 0;

$result = $mysqli->prepare("SELECT faculty.id, name, COUNT(school) FROM faculty LEFT JOIN schools ON schools.facultyID=faculty.id WHERE faculty.deleted IS NULL GROUP BY name ORDER BY name");
$result->execute();
$result->bind_result($id, $name, $school_no);
while ($result->fetch()) {
  echo "<tr id=\"$id\" onclick=\"selLine($id, event)\" ondblclick=\"editFaculty()\" class=\"l\"><td><div class=\"col10\">$name</div></td><td class=\"col10\" style=\"text-align:right\">$school_no</td><td></td></tr>\n";
  $id++;
}
$result->close();

$mysqli->close();
?>
</tbody>
</table>
</div>

</body>
</html>