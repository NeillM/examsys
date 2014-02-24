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
  
  <title>Rog&#333;: <?php echo $string['newsannouncements'] . ' ' . $configObject->get('cfg_install_type'); ?></title>
  
  <link rel="stylesheet" type="text/css" href="../css/body.css" />
  <link rel="stylesheet" type="text/css" href="../css/header.css" />
  <link rel="stylesheet" type="text/css" href="../css/submenu.css" />
  <link rel="stylesheet" type="text/css" href="../css/list.css" />

  <script type="text/javascript" src="../js/jquery-1.6.1.min.js"></script>
  <script type="text/javascript" src="../js/staff_help.js"></script>
  <script type="text/javascript" src="../js/list.js"></script>
  <script type="text/javascript" src="../js/toprightmenu.js"></script>
  <script language="javascript">
    function edit(lineID) {
      document.location.href='./edit_announcement.php?announcementid=' + lineID;
    }
  </script>
</head>

<body>
<?php
  require '../include/announcement_options.inc';
  require '../include/toprightmenu.inc';
	
	echo draw_toprightmenu();
?>
<div id="content" class="content">

<table class="header">
<tr>
<th colspan="2"><div class="breadcrumb"><a href="../staff/index.php"><?php echo $string['home']; ?></a>&nbsp;&nbsp;<img src="../artwork/breadcrumb_arrow.png" width="4" height="7" alt="-" />&nbsp;&nbsp;<a href="./index.php"><?php echo $string['administrativetools']; ?></a></div><div style="margin-left:10px; font-size:200%; font-weight:bold"><?php echo $string['newsannouncements']; ?></th>
<th style="text-align:right; vertical-align:top"><img src="../artwork/toprightmenu.gif" id="toprightmenu_icon"></th>
</tr>
<tr>
<?php
  if (isset($_GET['sortby'])) {
    $sortby = $_GET['sortby'];
    $ordering = $_GET['ordering'];
  } else {
    $sortby = 'startdate';
    $ordering = 'desc';
  }

  // Output table header
  $table_order = array($string['title']=>'title', $string['startdate']=>'startdate', $string['enddate']=>'enddate');
  foreach($table_order as $display => $key) {
    if ($key == 'title') {
      echo '<th class="vert_div col10">';
    } else {
      echo '<th class="vert_div">';
    }
    if ($sortby == $key and $ordering == 'asc') {
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=desc\">$display</a>&nbsp;<img src=\"../artwork/desc.gif\" width=\"9\" height=\"7\" />";
    } elseif ($sortby == $key and $ordering == 'desc') {
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=asc\">$display</a>&nbsp;<img src=\"../artwork/asc.gif\" width=\"9\" height=\"7\" />";
    } else {
      echo "<a href=\"" . $_SERVER['PHP_SELF'] . "?sortby=$key&ordering=asc\">$display</a>";
    }
		echo "</th>";
  }
?>
</tr>
<?php
$announce_no = 0;
$announcements = array();

$result = $mysqli->prepare("SELECT id, title, startdate, DATE_FORMAT(startdate, '" . $configObject->get('cfg_long_date_time') . "') AS startdate_display, DATE_FORMAT(enddate, '" . $configObject->get('cfg_long_date_time') . "') AS enddate_display FROM announcements WHERE deleted IS NULL ORDER BY $sortby $ordering");
$result->execute();
$result->bind_result($announcementid, $title, $startdate, $startdate_display, $enddate_display);
while ($result->fetch()) {
  $announcements[$announce_no]['announcementid'] = $announcementid;
  $announcements[$announce_no]['title'] = $title;
  $announcements[$announce_no]['startdate_display'] = $startdate_display;
  $announcements[$announce_no]['enddate_display'] = $enddate_display;
  
  $announce_no++;
}
$result->close();

for ($i=0; $i<$announce_no; $i++) {
  echo "<tr id=\"" . $announcements[$i]['announcementid'] . "\" onclick=\"selLine('" . $announcements[$i]['announcementid'] . "',event)\" ondblclick=\"edit('" . $announcements[$i]['announcementid'] . "')\" class=\"l\"><td><div class=\"col10\">" . $announcements[$i]['title'] . "</div></td><td><div class=\"col\">" . $announcements[$i]['startdate_display']  . "</div></td><td><div class=\"col\">" . $announcements[$i]['enddate_display']  . "</div></td></tr>\n";
}

$mysqli->close();
?>
</table>
</div>

</body>
</html>