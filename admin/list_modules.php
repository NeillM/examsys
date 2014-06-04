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
  
  <title>Rog&#333;: <?php echo $string['modules'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />
	<style>
	  th a {color:black !important}
	</style>

  <script type="text/javascript" src="../js/jquery-1.11.1.min.js"></script>
  <script type="text/javascript" src="../js/jquery_tablesorter/jquery.tablesorter.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/list.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script language="javascript">
    function edit(moduleID) {
      document.location.href = './edit_module.php?moduleid=' + moduleID;
    }
    
    $(document).ready(function() {
      $("#maindata").tablesorter({ 
        sortList: [[0,0]] 
      });

    });
  </script>
</head>

<body>
<?php
  require '../include/admin_module_options.inc';
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu(233);
?>
<div id="content" class="content">

<div class="head_title">
  <img src="../artwork/toprightmenu.gif" id="toprightmenu_icon" />
  <div class="breadcrumb"><a href="../index.php"><?php echo $string['home']; ?></a><img src="../artwork/breadcrumb_arrow.png" class="breadcrumb_arrow" alt="-" /><a href="./index.php"><?php echo $string['administrativetools']; ?></a></div>
  <div class="page_title"><?php echo $string['modules'] ?></div>
</div>

<table id="maindata" class="header tablesorter" cellspacing="0" cellpadding="2" border="0" style="width:100%">
<thead>
<tr>
  <th class="col" style="width:15%"><?php echo $string['moduleid'] ?></th>
  <th class="col" style="width:30%"><?php echo $string['name'] ?></th>
  <th class="col" style="width:30%"><?php echo $string['school'] ?></th>
  <th class="col" style="width:15%"><?php echo $string['active'] ?></th>
</tr>
</thead>

<tbody>
<?php
$id = 0;
$module_no = 0;

$modules = array();

$result = $mysqli->prepare("SELECT modules.id, moduleid, fullname, school, active FROM modules, schools WHERE modules.schoolid = schools.id AND mod_deleted IS NULL");
$result->execute();
$result->bind_result($id, $moduleid, $fullname, $school, $active);
while ($result->fetch()) {
  $modules[$module_no]['id'] = $id;
  $modules[$module_no]['moduleid'] = $moduleid;
  $modules[$module_no]['name'] = $fullname;
  $modules[$module_no]['school'] = $school;
  $modules[$module_no]['active'] = $active;
  
  $module_no++;
}

for ($i=0; $i<$module_no; $i++) {
  if ($modules[$i]['school'] == '') $modules[$i]['school'] = '<span style="color:#808080">unknown</span>';
  if ($modules[$i]['active'] == 1) {
    $tmp_active = $string['yes'];
		$class = 'l';
  } else {
    $tmp_active = $string['no'];
		$class = 'l grey';
  }
  
	echo "<tr class=\"$class\" id=\"" . $modules[$i]['id'] . "\" onclick=\"selLine('" . $modules[$i]['id'] . "',event)\" ondblclick=\"edit('" . $modules[$i]['id'] . "')\"><td><div class=\"col\">" . $modules[$i]['moduleid'] . "</div></td><td><div class=\"col\">" . $modules[$i]['name'] . "</div></td><td><div class=\"col\"><nobr>" . $modules[$i]['school'] . "</nobr></div></td><td><div class=\"col\">$tmp_active</div></td></tr>\n";
}
$result->close();
$mysqli->close();
?>
</tbody>
</table>
</div>

</body>
</html>